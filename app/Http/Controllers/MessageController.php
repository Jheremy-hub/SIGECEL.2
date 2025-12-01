<?php

namespace App\Http\Controllers;

use App\Mail\NewMessageMail;
use App\Models\MessageApproval;
use App\Models\User;
use App\Models\UserMessage;
use App\Models\UserMessageForward;
use App\Models\UserMessageLog;
use App\Models\UserRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MessageController extends Controller
{
    public function sige(Request $request)
    {
        $user = Auth::user()->load('role', 'documents');
        $userId = $user->id;

        $userRoleName = optional($user->role)->role ?? '';
        $normalizedRole = Str::lower(Str::ascii($userRoleName));
        $isPublicOnly = ($normalizedRole === 'usuario');

        // Para usuarios rol "Usuario": habilitar búsqueda con la misma lógica de Seguimiento
        if ($isPublicOnly) {
            $codeInput = trim((string) $request->input('code'));
            $message   = null;
            $segments  = [];
            $areaSegments = [];
            $error     = null;
            $publicLogs = collect();

            if ($codeInput !== '') {
                $message = $this->findMessageByCode($codeInput);
                if (!$message) {
                    $error = 'No se encontró ningún documento con ese código.';
                } else {
                    $logs       = $message->logs->sortBy('created_at')->values();
                    $segments   = $this->buildHolderSegments($message, $logs);

                    $senderRole = optional(optional($message->sender)->role)->role;
                    if ($senderRole) {
                        array_unshift($segments, [
                            'holder_id'     => $message->sender_id,
                            'from'          => $message->created_at,
                            'to'            => $segments[0]['from'] ?? $message->created_at,
                            'holder'        => trim(($message->sender->name ?? '') . ' ' . ($message->sender->apellidos ?? '')),
                            'role'          => $senderRole,
                            'status_action' => 'in_review',
                        ]);
                    }

                    $areaSegments = $segments;
                    $publicLogs = $this->buildPublicLogs($logs);
                }
            }

            return view('messages.tracking', [
                'code'        => $codeInput,
                'message'     => $message,
                'segments'    => $segments,
                'areaSegments'=> $areaSegments,
                'publicLogs'  => $publicLogs,
                'error'       => $error,
                'pageTitle'   => 'SIGECEL',
                'pageSubtitle'=> 'Consulta el estado de tu documento',
            ]);
        }

        $inboxMessages = UserMessage::with(['sender.role', 'logs'])
            ->where(function ($q) use ($userId) {
                $q->where('receiver_id', $userId)
                    ->orWhereHas('logs', function ($logQ) use ($userId) {
                        $logQ->where('user_id', $userId)
                            ->where('action', '!=', 'sent');
                    });
            })
            ->orderByDesc('created_at')
            ->get();

        $sentMessages = UserMessage::with(['receiver.role', 'logs'])
            ->where('sender_id', $userId)
            ->orderByDesc('created_at')
            ->get();

        // Solo mostrar pendientes donde soy el aprobador designado y receptor actual
        $pendingApprovals = UserMessage::with(['sender.role', 'receiver.role'])
            ->where('receiver_id', $userId)
            ->where('approver_id', $userId)
            ->where('status', 'pendiente_aprobacion_jefe')
            ->orderByDesc('created_at')
            ->get();

        return view('sige.index', compact('user', 'inboxMessages', 'sentMessages', 'pendingApprovals', 'isPublicOnly'));
    }

    public function getComposeForm()
    {
        $users = User::where('id', '!=', Auth::id())
            ->where('id_estado', 1)
            ->with('role')
            ->get();
        $roleOptions = $this->getUniqueRoleOptions();
        return view('sige.partials.compose', compact('users', 'roleOptions'));
    }

    public function create()
    {
        $users = User::where('id', '!=', Auth::id())
            ->where('id_estado', 1)
            ->with('role')
            ->get();
        return view('messages.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:cms_users,id',
            'subject'     => 'required|string|max:255',
            'message'     => 'required|string',
            'urgency'     => 'nullable|string|in:normal,alta,critica',
            'file'        => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,txt,zip,rar',
        ]);

        DB::beginTransaction();
        try {
            $fileData = [];
            if ($request->hasFile('file')) {
                $file     = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $fileData = [
                    'file_path' => $file->storeAs('user_messages', $fileName, 'public'),
                    'file_name' => $fileName,
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ];
            }

            $targetReceiver = (int) $request->input('receiver_id');
            $approverId = $this->findBossForUser(Auth::user());

            $receiverId = $targetReceiver;
            $intendedReceiverId = null;
            $status = 'sent';
            $approver = null;

            // Solo enviamos a aprobación si existe un jefe distinto al remitente
            if ($approverId && $approverId !== Auth::id()) {
                $receiverId = $approverId;
                $intendedReceiverId = $targetReceiver;
                $approver = $approverId;
                $status = 'pendiente_aprobacion_jefe';
            }

            $message = UserMessage::create(array_merge([
                'sender_id'            => Auth::id(),
                'receiver_id'          => $receiverId,
                'intended_receiver_id' => $intendedReceiverId,
                'approver_id'          => $approver,
                'subject'              => $request->input('subject'),
                'message'              => $request->input('message'),
                'is_read'              => 0,
                'status'               => $status,
            ], $fileData));

            UserMessageLog::create([
                'message_id' => $message->id,
                'user_id'    => Auth::id(),
                'action'     => 'sent',
                'details'    => json_encode([
                    'to'                => $targetReceiver,
                    'urgency'           => $request->input('urgency', 'normal'),
                    'approver_id'       => $approver,
                    'intended_receiver' => $intendedReceiverId,
                ]),
            ]);

            DB::commit();

            $emailSent = false;
            $emailError = null;
            try {
                $notifyUser = null;
                if ($status === 'pendiente_aprobacion_jefe' && $approver) {
                    $notifyUser = User::find($approver);
                } else {
                    $notifyUser = $message->receiver()->first();
                }
                if ($notifyUser && !empty($notifyUser->email)) {
                    \Log::info('SIGE correo: intentando enviar a ' . $notifyUser->email);
                    Mail::to($notifyUser->email)->send(new NewMessageMail($message));
                    $emailSent = true;
                }
            } catch (\Throwable $e) {
                $emailError = $e->getMessage();
                \Log::warning('Fallo de correo (store): ' . $emailError);
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success'    => true,
                    'message'    => $status === 'pendiente_aprobacion_jefe' ? 'Mensaje enviado a aprobación de jefe' : 'Mensaje enviado correctamente',
                    'status'     => $status,
                    'email_sent' => $emailSent,
                    'email_error'=> $emailError,
                    'message_id' => $message->id,
                ]);
            }

            $flash = $status === 'pendiente_aprobacion_jefe'
                ? 'Mensaje enviado al jefe para aprobación'
                : 'Mensaje enviado';
            if (!$emailSent && $emailError) {
                $flash .= ' (correo no enviado: ' . $emailError . ')';
            }
            return redirect()->route('messages.inbox')->with('success', $flash);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Error al enviar el mensaje: ' . $e->getMessage())->withInput();
        }
    }

    public function inbox()
    {
        $messages = UserMessage::with('sender')
            ->where('receiver_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();
        return view('messages.inbox', compact('messages'));
    }

    public function sent()
    {
        $messages = UserMessage::with('receiver')
            ->where('sender_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();
        return view('messages.sent', compact('messages'));
    }

    public function show(int $id)
    {
        $message = UserMessage::with(['sender.role', 'receiver.role', 'logs.user.role'])->findOrFail($id);

        $userId = Auth::id();
        $isSender = $message->sender_id === $userId;
        $isCurrentReceiver = $message->receiver_id === $userId;
        $hasParticipated = $message->logs->contains(function ($log) use ($userId) {
            return (int) $log->user_id === (int) $userId;
        });

        if (!$isSender && !$isCurrentReceiver && !$hasParticipated) {
            abort(403);
        }

        if ($isCurrentReceiver && !$message->is_read) {
            $message->update(['is_read' => 1]);
        }

        $forwards = UserMessageForward::with(['forwardedBy.role', 'forwardedTo.role'])
            ->where('original_message_id', $id)
            ->get();

        $users = User::where('id', '!=', Auth::id())
            ->where('id_estado', 1)
            ->with('role')
            ->get();

        $participantIds = collect([
            $message->sender_id,
            $message->receiver_id,
        ]);

        foreach ($message->logs as $log) {
            if (!empty($log->user_id)) {
                $participantIds->push((int) $log->user_id);
            }

            $details = is_string($log->details)
                ? json_decode($log->details, true)
                : (is_array($log->details) ? $log->details : []);

            if (is_array($details)) {
                foreach (['previous_receiver_id', 'new_receiver_id', 'assigned_to_id', 'to_user_id', 'to'] as $key) {
                    if (!empty($details[$key])) {
                        $participantIds->push((int) $details[$key]);
                    }
                }
            }
        }

        $participantIds = $participantIds
            ->filter(function ($idValue) {
                return !empty($idValue);
            })
            ->unique()
            ->values();

        $participants = $participantIds->isEmpty()
            ? collect()
            : User::whereIn('id', $participantIds)->with('role')->get();

        return view('messages.show_page', compact('message', 'users', 'forwards', 'participants'));
    }

    public function download(int $id)
    {
        $message = UserMessage::with('logs')->findOrFail($id);
        $userId = Auth::id();

        $isSender = $message->sender_id === $userId;
        $isCurrentReceiver = $message->receiver_id === $userId;
        $hasParticipated = $message->logs->contains(function ($log) use ($userId) {
            return (int) $log->user_id === (int) $userId;
        });

        if (!$isSender && !$isCurrentReceiver && !$hasParticipated) {
            abort(403);
        }
        if (!$message->file_path) {
            return back()->with('error', 'No hay archivo adjunto');
        }

        UserMessageLog::create([
            'message_id' => $id,
            'user_id'    => Auth::id(),
            'action'     => 'downloaded',
            'details'    => json_encode(['file_name' => $message->file_name]),
        ]);

        return Storage::disk('public')->download($message->file_path, $message->file_name);
    }

    public function destroy(int $id)
    {
        $message = UserMessage::findOrFail($id);
        if ($message->sender_id !== Auth::id() && $message->receiver_id !== Auth::id()) {
            abort(403);
        }
        if ($message->file_path) {
            Storage::disk('public')->delete($message->file_path);
        }
        $message->delete();
        return back()->with('success', 'Mensaje eliminado');
    }

    public function markAsRead(int $id): JsonResponse
    {
        try {
            $message = UserMessage::findOrFail($id);
            if ($message->receiver_id !== Auth::id() && $message->sender_id !== Auth::id()) {
                return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
            }
            if (!$message->is_read && $message->receiver_id === Auth::id()) {
                $message->update(['is_read' => 1]);
                UserMessageLog::create([
                    'message_id' => $id,
                    'user_id'    => Auth::id(),
                    'action'     => 'read',
                ]);
            }
            return response()->json(['success' => true]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Mensaje no encontrado'], 404);
        }
    }

    public function forward(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'new_receiver_id' => 'required|exists:cms_users,id',
            'forward_note'    => 'nullable|string|max:500',
        ]);

        $originalMessage = UserMessage::findOrFail($id);

        if ($originalMessage->receiver_id !== Auth::id()) {
            abort(403);
        }
        if ((int) $request->input('new_receiver_id') === (int) Auth::id()) {
            return response()->json(['success' => false, 'message' => 'No puedes reenviarte el documento a ti mismo.'], 422);
        }

        $previousReceiverId = $originalMessage->receiver_id;
        $originalMessage->update(['receiver_id' => (int) $request->input('new_receiver_id')]);

        UserMessageForward::create([
            'original_message_id'  => $id,
            'forwarded_message_id' => $id,
            'forwarded_by'         => Auth::id(),
            'forwarded_to'         => (int) $request->input('new_receiver_id'),
        ]);

        $newReceiver      = User::find((int) $request->input('new_receiver_id'));
        $previousReceiver = User::find($previousReceiverId);

        UserMessageLog::create([
            'message_id' => $id,
            'user_id'    => Auth::id(),
            'action'     => 'forwarded',
            'details'    => json_encode([
                'previous_receiver_id'   => $previousReceiverId,
                'previous_receiver_name' => $previousReceiver ? trim(($previousReceiver->name ?? '') . ' ' . ($previousReceiver->apellidos ?? '')) : null,
                'new_receiver_id'        => $newReceiver->id ?? (int) $request->input('new_receiver_id'),
                'new_receiver_name'      => $newReceiver ? trim(($newReceiver->name ?? '') . ' ' . ($newReceiver->apellidos ?? '')) : null,
                'new_receiver_role'      => optional($newReceiver->role)->role,
                'forward_note'           => trim((string) $request->input('forward_note')) ?: null,
            ]),
        ]);

        return response()->json(['success' => true, 'message' => 'Mensaje reenviado correctamente.']);
    }

    public function reply(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'reply_text'  => 'nullable|string',
            'reply_file'  => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,txt',
            'reply_to_id' => 'nullable|integer|exists:cms_users,id',
        ]);

        $message = UserMessage::findOrFail($id);
        if ($message->sender_id !== Auth::id() && $message->receiver_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $fileData = null;
        if ($request->hasFile('reply_file')) {
            $file     = $request->file('reply_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path     = $file->storeAs('user_messages/replies', $fileName, 'public');
            $fileData = [
                'path' => $path,
                'name' => $fileName,
                'type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ];
        }

        $details = ['text' => trim((string) $request->input('reply_text'))];
        if ($request->filled('reply_to_id')) {
            $to = User::find((int) $request->input('reply_to_id'));
            if ($to) {
                $details['to_user_id']   = $to->id;
                $details['to_user_name'] = trim(($to->name ?? '') . ' ' . ($to->apellidos ?? ''));
                $details['to_user_role'] = optional($to->role)->role;
            }
        }
        if ($fileData) {
            $details['file'] = $fileData;
        }

        $log = UserMessageLog::create([
            'message_id' => $message->id,
            'user_id'    => Auth::id(),
            'action'     => 'reply',
            'details'    => json_encode($details),
        ]);

        return response()->json(['success' => true, 'message' => 'Respuesta registrada', 'log_id' => $log->id]);
    }

    public function downloadReply(int $id, int $logId)
    {
        $message = UserMessage::findOrFail($id);
        $log     = UserMessageLog::where('id', $logId)->where('message_id', $id)->firstOrFail();
        if ($log->action !== 'reply') {
            abort(404);
        }
        if ($message->sender_id !== Auth::id() && $message->receiver_id !== Auth::id()) {
            abort(403);
        }
        $details = is_string($log->details) ? json_decode($log->details, true) : [];
        if (empty($details['file']['path'])) {
            abort(404);
        }
        return Storage::disk('public')->download($details['file']['path'], $details['file']['name'] ?? 'reply');
    }

    public function report(int $id)
    {
        $message = UserMessage::with(['sender', 'receiver', 'logs.user.role'])->findOrFail($id);
        $userId = Auth::id();

        $esRemitente       = ($message->sender_id === $userId);
        $esReceptorActual  = ($message->receiver_id === $userId);
        $haParticipado     = $message->logs->contains(function ($log) use ($userId) {
            return (int) $log->user_id === (int) $userId;
        });

        if (!($esRemitente || $esReceptorActual || $haParticipado)) {
            abort(403);
        }

        return view('messages.report', compact('message'));
    }

    public function publicTracking(Request $request)
    {
        $codeInput = trim((string) $request->input('code'));
        $message   = null;
        $segments  = [];
        $areaSegments = [];
        $error     = null;
        $publicLogs = collect();

        if ($codeInput !== '') {
            $message = $this->findMessageByCode($codeInput);
            if (!$message) {
                $error = 'No se encontro ningun documento con ese codigo.';
            } else {
                $logs       = $message->logs->sortBy('created_at')->values();
                $segments   = $this->buildHolderSegments($message, $logs);

                $senderRole = optional(optional($message->sender)->role)->role;
                if ($senderRole) {
                    array_unshift($segments, [
                        'holder_id'     => $message->sender_id,
                        'from'          => $message->created_at,
                        'to'            => $segments[0]['from'] ?? $message->created_at,
                        'holder'        => trim(($message->sender->name ?? '') . ' ' . ($message->sender->apellidos ?? '')),
                        'role'          => $senderRole,
                        'status_action' => 'in_review',
                    ]);
                }

                $areaSegments = $segments;
                $publicLogs = $this->buildPublicLogs($logs);
            }
        }

        return view('messages.tracking', [
            'code'     => $codeInput,
            'message'  => $message,
            'segments' => $segments,
            'areaSegments' => $areaSegments,
            'publicLogs' => $publicLogs,
            'error'    => $error,
        ]);
    }

    protected function findMessageByCode(string $code): ?UserMessage
    {
        $code = strtoupper(trim($code));
        $code = str_replace(' ', '', $code);
        $code = preg_replace('/R-/', '-', $code);

        $id = null;

        if (preg_match('/^(\d{1,5})-\d{2}$/', $code, $m)) {
            $id = (int) $m[1];
        } elseif (preg_match('/^\d{1,5}$/', $code, $m)) {
            $id = (int) $m[0];
        }

        if (!$id) {
            return null;
        }

        return UserMessage::with(['sender.role', 'receiver.role', 'logs.user.role'])->find($id);
    }

    protected function buildHolderSegments(UserMessage $message, $logs): array
    {
        $segments = [];
        $logs     = $logs instanceof \Illuminate\Support\Collection
            ? $logs->sortBy('created_at')->values()
            : collect($logs)->sortBy('created_at')->values();

        if ($logs->isEmpty()) {
            return $segments;
        }

        $firstLog = $logs->first();
        $sentLog  = $logs->firstWhere('action', 'sent');

        $holderId = null;
        if ($sentLog) {
            $sd = is_string($sentLog->details)
                ? json_decode($sentLog->details, true)
                : (is_array($sentLog->details) ? $sentLog->details : []);
            $holderId = $sd['to'] ?? $message->receiver_id;
        } else {
            $holderId = $message->receiver_id;
        }

        $start = $firstLog->created_at ?? $message->created_at;

        foreach ($logs as $lg) {
            if ($lg->action === 'forwarded') {
                $segments[] = [
                    'holder_id' => $holderId,
                    'from'      => $start,
                    'to'        => $lg->created_at,
                ];

                $d = is_string($lg->details)
                    ? json_decode($lg->details, true)
                    : (is_array($lg->details) ? $lg->details : []);

                $holderId = $d['new_receiver_id'] ?? $holderId;
                $start    = $lg->created_at;
            }
        }

        $segments[] = [
            'holder_id' => $holderId,
            'from'      => $start,
            'to'        => $logs->last()->created_at ?? now(),
        ];

        foreach ($segments as &$seg) {
            $u = User::with('role')->find($seg['holder_id']);
            $seg['holder'] = $u ? trim(($u->name ?? '') . ' ' . ($u->apellidos ?? '')) : null;
            $seg['role']   = $u && $u->role ? $u->role->role : null;

            $from = Carbon::parse($seg['from']);
            $to   = Carbon::parse($seg['to']);
            $segmentLogs = $logs->filter(function ($log) use ($from, $to) {
                $ts = Carbon::parse($log->created_at);
                return $ts >= $from && $ts <= $to;
            })->sortBy('created_at')->values();

            $businessActions = ['in_review', 'approved', 'observed', 'finalized', 'archived', 'cancelled'];

            $businessLog = $segmentLogs->filter(function ($log) use ($businessActions) {
                return in_array($log->action, $businessActions, true);
            })->last();

            if ($businessLog) {
                $seg['status_action'] = $businessLog->action;
            } else {
                $lastLog = $segmentLogs->last();
                $seg['status_action'] = $lastLog ? $lastLog->action : 'in_review';
            }
        }
        unset($seg);

        return $segments;
    }

    protected function summarizeSegmentsByArea(array $segments): array
    {
        $collection = collect($segments)->filter(function ($seg) {
            return !empty($seg['role']);
        });

        if ($collection->isEmpty()) {
            return [];
        }

        $rank = [
            'cancelled'  => 6,
            'archived'   => 5,
            'finalized'  => 4,
            'approved'   => 3,
            'observed'   => 2,
            'in_review'  => 1,
        ];

        $summary = $collection
            ->groupBy('role')
            ->map(function ($group) use ($rank) {
                $sorted = $group->sortBy(function ($seg) {
                    return Carbon::parse($seg['from']);
                });
                $first = $sorted->first();

                $bestAction = 'in_review';
                $bestScore  = 0;

                foreach ($group as $seg) {
                    $action = $seg['status_action'] ?? null;
                    if (!$action) {
                        continue;
                    }
                    $score = $rank[$action] ?? 0;
                    if ($score > $bestScore) {
                        $bestScore  = $score;
                        $bestAction = $action;
                    }
                }

                return [
                    'role'          => $first['role'],
                    'from'          => $first['from'],
                    'status_action' => $bestAction,
                ];
            })
            ->values()
            ->sortBy(function ($seg) {
                return Carbon::parse($seg['from']);
            })
            ->values()
            ->all();

        return $summary;
    }

    protected function buildPublicLogs($logs)
    {
        $logs = $logs instanceof \Illuminate\Support\Collection
            ? $logs->sortBy('created_at')->values()
            : collect($logs)->sortBy('created_at')->values();

        return $logs->map(function ($log) {
            $area = optional(optional($log->user)->role)->role;
            $accion = match ($log->action) {
                'sent'       => 'Registrado',
                'read'       => 'Recibido',
                'downloaded' => 'Descargado',
                'in_review'  => 'En revision',
                'approved'   => 'Aprobado',
                'observed'   => 'Observado',
                'finalized'  => 'Finalizado',
                'archived'   => 'Archivado',
                'cancelled'  => 'Anulado',
                'forwarded'  => 'Derivado / Reenviado',
                'reply'      => 'Respuesta registrada',
                default      => ucfirst($log->action),
            };

            return [
                'date'   => $log->created_at,
                'area'   => $area,
                'action' => $accion,
            ];
        });
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'action'     => 'required|string|in:in_review,approved,observed,finalized,archived,cancelled',
            'to_user_id' => 'nullable|integer|exists:cms_users,id',
        ]);

        $message = UserMessage::findOrFail($id);

        if ($message->receiver_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'No autorizado para cambiar el estado'], 403);
        }

        if ($message->logs()->whereIn('action', ['archived', 'cancelled'])->exists()) {
            return response()->json(['success' => false, 'message' => 'El documento esta cerrado y no puede cambiar de estado'], 422);
        }

        $hasFinalized = $message->logs()->where('action', 'finalized')->exists();
        if ($hasFinalized && $request->input('action') !== 'archived') {
            return response()->json(['success' => false, 'message' => 'El documento esta finalizado y solo puede archivarse'], 422);
        }

        $details = [];
        if ($request->filled('to_user_id')) {
            $to = User::find((int) $request->input('to_user_id'));
            if ($to) {
                $details['assigned_to_id']   = $to->id;
                $details['assigned_to_name'] = trim(($to->name ?? '') . ' ' . ($to->apellidos ?? ''));
                $details['assigned_to_role'] = optional($to->role)->role;
            }
        }

        UserMessageLog::create([
            'message_id' => $message->id,
            'user_id'    => Auth::id(),
            'action'     => $request->input('action'),
            'details'    => empty($details) ? null : json_encode($details),
        ]);

        return response()->json(['success' => true, 'message' => 'Estado actualizado']);
    }

    public function approveAsBoss(Request $request, int $id): JsonResponse
    {
        $message = UserMessage::findOrFail($id);
        if ($message->receiver_id !== Auth::id() || $message->status !== 'pendiente_aprobacion_jefe') {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $finalReceiver = $message->intended_receiver_id ?: $message->receiver_id;

        $message->update([
            'receiver_id'          => $finalReceiver,
            'intended_receiver_id' => null,
            'status'               => 'aprobado_por_jefe',
            'is_read'              => 0,
        ]);

        MessageApproval::create([
            'message_id'  => $message->id,
            'approver_id' => Auth::id(),
            'decision'    => 'approve',
            'note'        => null,
            'decided_at'  => now(),
        ]);

        UserMessageLog::create([
            'message_id' => $message->id,
            'user_id'    => Auth::id(),
            'action'     => 'approved_by_boss',
            'details'    => json_encode(['to' => $finalReceiver]),
        ]);

        return response()->json(['success' => true, 'message' => 'Aprobado y enviado al destinatario']);
    }

    public function archiveAsBoss(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'note' => 'required|string|max:500',
        ]);

        $message = UserMessage::findOrFail($id);
        if ($message->receiver_id !== Auth::id() || $message->status !== 'pendiente_aprobacion_jefe') {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $message->update([
            'status'  => 'archivado_por_jefe',
            'is_read' => 1,
        ]);

        MessageApproval::create([
            'message_id'  => $message->id,
            'approver_id' => Auth::id(),
            'decision'    => 'archive',
            'note'        => $data['note'],
            'decided_at'  => now(),
        ]);

        UserMessageLog::create([
            'message_id' => $message->id,
            'user_id'    => Auth::id(),
            'action'     => 'archived_by_boss',
            'details'    => json_encode(['note' => $data['note']]),
        ]);

        return response()->json(['success' => true, 'message' => 'Documento archivado por el jefe']);
    }

    protected function findBossForUser(User $user): ?int
    {
        $role = $user->role;
        if (!$role || !$role->parent_role_id) {
            return null;
        }

        $parent = UserRole::find($role->parent_role_id);
        if (!$parent) {
            return null;
        }

        if (!empty($parent->user_id)) {
            return (int) $parent->user_id;
        }

        $candidate = UserRole::where('role', $parent->role)
            ->whereNotNull('user_id')
            ->orderBy('hierarchy_level')
            ->value('user_id');

        return $candidate ? (int) $candidate : null;
    }
}
