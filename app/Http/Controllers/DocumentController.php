<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserDocument;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class DocumentController extends Controller
{
    public function create()
    {
        $type = request('type');
        if ($type === 'Oficio') {
            return view('documents.create-oficio');
        } elseif ($type === 'Memo') {
            return view('documents.create-memo');
        } elseif ($type === 'Carta') {
            return view('documents.create-carta');
        }
        abort(404, 'Tipo de documento no válido');
    }

    public function store(Request $request)
{
    // Validaciones genéricas
    $request->validate([
        'document_type' => 'required|in:Memo,Carta,Oficio',
        'institution' => 'required|string|max:255',
        'subject' => 'required|string|max:255',
        'content' => 'nullable|string',
        'file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,txt,xlsx,xls'
    ]);

    // Validaciones específicas por tipo
    if ($request->document_type === 'Oficio') {
        $request->validate([
            'oficio_mode' => 'required|in:Simple,Múltiple',
            'sent_by' => 'required|string|max:255',
            'acceptance_date' => 'required|date',
        ]);
    } elseif ($request->document_type === 'Memo') {
        $request->validate([
            'memo_mode' => 'required|in:Simple,Múltiple',
            'sent_by' => 'required|string|max:255',
            'acceptance_date' => 'required|date',
        ]);
    } elseif ($request->document_type === 'Carta') {
        $request->validate([
            'carta_mode' => 'required|in:Simple,Múltiple',
            'sent_by' => 'required|string|max:255',
            'acceptance_date' => 'required|date',
        ]);
    }

    try {
        DB::beginTransaction();

        // Generar código único por tipo y año
        $year = date('y');
        $countThisYear = UserDocument::where('document_type', $request->document_type)
            ->whereYear('created_at', date('Y'))
            ->count();
        $nextNumber = $countThisYear + 1;
        $documentCode = str_pad($nextNumber, 4, '0', STR_PAD_LEFT) . '-' . $year;

        // Procesar archivo
        $fileData = [];
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $fileData = [
                'file_path' => $file->storeAs('user_documents', $fileName, 'public'),
                'file_name' => $fileName,
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
            ];
        }

        // Preparar metadatos por tipo
        $meta = [];
        if ($request->document_type === 'Oficio') {
            $meta = [
                'mode' => $request->oficio_mode,
                'sent_by' => $request->sent_by,
                'acceptance_date' => $request->acceptance_date,
            ];
            $sender = $request->sent_by;
        } else {
            $meta = [
                // Para Memo y Carta, se completará más abajo según tipo
            ];
            if ($request->document_type === 'Carta') {
                // Carta: usar únicamente las opciones solicitadas
                $meta = [
                    'mode' => $request->carta_mode,
                    'sent_by' => $request->sent_by,
                    'acceptance_date' => $request->acceptance_date,
                ];
            }
            if ($request->document_type === 'Memo') {
                $meta['mode'] = $request->memo_mode;
                $meta['sent_by'] = $request->sent_by;
                $meta['acceptance_date'] = $request->acceptance_date;
            }
            // Asignar "sender" (remitente visible) según tipo solicitado
            if ($request->document_type === 'Carta' || $request->document_type === 'Memo') {
                $sender = $request->sent_by;
            } else {
                $sender = $request->sender;
            }
        }

        // Crear el documento
        $document = UserDocument::create([
            'user_id' => Auth::id(),
            'document_type' => $request->document_type,
            'sender' => $sender,
            'institution' => $request->institution,
            'subject' => $request->subject,
'content' => $request->input('content'),            'document_code' => $documentCode,
            'meta' => json_encode($meta), // ✅ Guardar metadatos como JSON
        ] + $fileData);

        DB::commit();
        return redirect()->route('documents.index')->with('success', 'Documento guardado correctamente.');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Error al guardar el documento: ' . $e->getMessage())->withInput();
    }
    }

    public function index()
    {
        $user = Auth::user();
        $rol  = optional($user->role)->role;

        if (!in_array($rol, ['Secretaría', 'Recepción'])) {
            abort(403, 'No autorizado para acceder a Mis Documentos');
        }

        return view('documents.index');
    }

    public function getDocumentsByType(Request $request)
    {
        $user = Auth::user();
        $type = $request->input('type');

        if (!in_array($type, ['Oficio', 'Memo', 'Carta'])) {
            return response()->json(['error' => 'Tipo inválido'], 400);
        }

        $query = UserDocument::where('user_id', $user->id)->where('document_type', $type);

        if ($request->filled('code')) {
            $query->where('document_code', 'like', '%' . $request->code . '%');
        }
        if ($request->filled('sender')) {
            $query->where('sender', 'like', '%' . $request->sender . '%');
        }
        if ($request->filled('institution')) {
            $query->where('institution', 'like', '%' . $request->institution . '%');
        }
        if ($request->filled('subject')) {
            $query->where('subject', 'like', '%' . $request->subject . '%');
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $documents = $query->orderBy('created_at', 'desc')->get();
        $html = view('documents.partials.document-table', compact('documents', 'type'))->render();

        return response()->json(['success' => true, 'html' => $html, 'count' => $documents->count()]);
    }

    public function show($id)
    {
        $document = UserDocument::findOrFail($id);
        if ($document->user_id != Auth::id()) {
            abort(403);
        }
        return view('documents.show', compact('document'));
    }

    public function download($id)
    {
        $document = UserDocument::findOrFail($id);
        if ($document->user_id != Auth::id()) {
            abort(403);
        }
        if (!$document->file_path) {
            return redirect()->back()->with('error', 'No hay archivo adjunto');
        }
        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    public function destroy($id)
    {
        $document = UserDocument::findOrFail($id);
        if ($document->user_id != Auth::id()) {
            abort(403);
        }
        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }
        $document->delete();
        return redirect()->back()->with('success', 'Documento eliminado');
    }
}
