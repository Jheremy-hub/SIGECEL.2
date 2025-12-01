<?php

namespace App\Http\Controllers;

use App\Mail\BirthdayGreetingMail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class BirthdayReportController extends Controller
{
    public function index(Request $request)
    {
        $month = (int) $request->query('m', (int) date('n'));
        if ($month < 1 || $month > 12) {
            $month = (int) date('n');
        }

        $monthNames = [
            1  => 'Enero',
            2  => 'Febrero',
            3  => 'Marzo',
            4  => 'Abril',
            5  => 'Mayo',
            6  => 'Junio',
            7  => 'Julio',
            8  => 'Agosto',
            9  => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];

        $monthName = $monthNames[$month];

        // Asegurar juego de caracteres correcto
        DB::statement("SET NAMES 'utf8mb4'");

        // fecha_nacimiento viene como varchar en varios formatos, normalizamos con STR_TO_DATE
        $fn = "COALESCE(
            STR_TO_DATE(fecha_nacimiento, '%d/%m/%Y'),
            STR_TO_DATE(fecha_nacimiento, '%Y-%m-%d'),
            STR_TO_DATE(fecha_nacimiento, '%d-%m-%Y'),
            STR_TO_DATE(fecha_nacimiento, '%d.%m.%Y')
        )";

        $sql = "
            SELECT
                TRIM(nombre_completo) AS nombre,
                $fn AS fecha,
                DATE_FORMAT($fn, '%d/%m') AS dia_mes,
                DATE_FORMAT($fn, '%d') AS dia,
                TIMESTAMPDIFF(YEAR, $fn, CURDATE()) AS edad,
                nro_colegiado AS nro_colegiado,
                email AS correo,
                direccion AS direccion
            FROM colegiados
            WHERE $fn IS NOT NULL AND MONTH($fn) = ?
            ORDER BY DAY($fn), nombre
        ";

        $rawRows = DB::select($sql, [$month]);

        $rows = [];
        $todayDM = date('d/m');
        $todayDay = (int) date('j');
        $birthdaysToday = 0;

        foreach ($rawRows as $row) {
            $r = (array) $row;
            $r['es_hoy'] = ($r['dia_mes'] === $todayDM);
            if ($r['es_hoy']) {
                $birthdaysToday++;
            }
            $rows[] = $r;
        }

        $totalBirthdays = count($rows);

        // Cálculo de cumpleaños en la próxima semana y días restantes
        $birthdaysWeek = 0;
        $today = Carbon::today();
        $nextWeek = $today->copy()->addDays(7);

        foreach ($rows as &$r) {
            $day = (int) $r['dia'];
            $currentYear = (int) $today->format('Y');
            $birthdayThisYear = Carbon::create($currentYear, $month, $day, 0, 0, 0);
            if ($birthdayThisYear->lt($today)) {
                $birthdayThisYear->addYear();
            }
            $diffDays = $today->diffInDays($birthdayThisYear, false);
            $r['dias_restantes'] = max($diffDays, 0);

            if ($birthdayThisYear->betweenIncluded($today, $nextWeek)) {
                $birthdaysWeek++;
            }
        }
        unset($r);

        // Imagen de saludo vigente desde la tabla cumple_imagenes
        $todayDate = date('Y-m-d');
        $imageConfig = DB::table('cumple_imagenes')
            ->where('activo', 1)
            ->where('vigente_desde', '<=', $todayDate)
            ->where(function ($q) use ($todayDate) {
                $q->whereNull('vigente_hasta')
                  ->orWhere('vigente_hasta', '>=', $todayDate);
            })
            ->orderBy('vigente_desde', 'desc')
            ->first();

        $currentBackground = $imageConfig ? $imageConfig->ruta_imagen : 'Backend/Style/Ima.Cumple.jpg';

        // Texto de saludo configurable (si no existe en BD se usan valores por defecto)
        $defaultTitle = '¡Feliz cumpleaños!';
        $defaultMessage = 'El Colegio de Economistas de Lima le desea muchos éxitos y que tenga un gran día.';

        $greetingTitle = ($imageConfig && !empty($imageConfig->titulo))
            ? $imageConfig->titulo
            : $defaultTitle;

        $greetingMessage = ($imageConfig && !empty($imageConfig->mensaje))
            ? $imageConfig->mensaje
            : $defaultMessage;

        return view('reports.birthdays', [
            'rows'               => $rows,
            'month'              => $month,
            'monthName'          => $monthName,
            'totalBirthdays'     => $totalBirthdays,
            'birthdaysToday'     => $birthdaysToday,
            'birthdaysNextWeek'  => $birthdaysWeek,
            'todayDay'           => $todayDay,
            'currentBackground'  => $currentBackground,
            'greetingTitle'      => $greetingTitle,
            'greetingMessage'    => $greetingMessage,
        ]);
    }

    public function sendGreeting(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'name'  => 'required|string|max:255',
            'image' => 'nullable|string',
        ]);

        // Obtener configuración de saludo vigente
        $todayDate = date('Y-m-d');
        $imageConfig = DB::table('cumple_imagenes')
            ->where('activo', 1)
            ->where('vigente_desde', '<=', $todayDate)
            ->where(function ($q) use ($todayDate) {
                $q->whereNull('vigente_hasta')
                  ->orWhere('vigente_hasta', '>=', $todayDate);
            })
            ->orderBy('vigente_desde', 'desc')
            ->first();

        if (!$imageConfig) {
            
            \Log::error("No se encontró configuración de imagen activa para el saludo de cumpleaños.");
            return response()->json([
                'ok' => false,
                'error' => 'No se encontró configuración de imagen activa para el saludo de cumpleaños.',
            ], 500);
        }

        $title = $imageConfig && !empty($imageConfig->titulo)
            ? $imageConfig->titulo
            : '¡Feliz cumpleaños!';

        $messageText = $imageConfig && !empty($imageConfig->mensaje)
            ? $imageConfig->mensaje
            : 'El Colegio de Economistas de Lima le desea muchos éxitos y que tenga un gran día.';

        // Imagen ya compuesta enviada desde el navegador (data URL base64)
        $imageData = null;
        $imageMime = 'image/png';

        if ($request->filled('image')) {
            $dataUrl = (string) $request->input('image');

            if (strpos($dataUrl, 'base64,') !== false) {
                [$meta, $encoded] = explode('base64,', $dataUrl, 2);

                // Regex corregido: ^data:(image/[^;]+);base64
                if (preg_match('/^data:(image\/[^;]+);base64/', $meta, $m)) {
                    $imageMime = $m[1];
                }

                $decoded = base64_decode($encoded, true);
                if ($decoded !== false) {
                    $imageData = $decoded;
                } else {
                    \Log::error("Error al decodificar la imagen base64 enviada.");
                    return response()->json([
                        'ok' => false,
                        'error' => 'Error al procesar la imagen enviada.',
                    ], 422);
                }
            }
        }

        try {
            \Log::info("Intentando enviar saludo de cumpleaños", [
                'email' => $request->email,
                'name' => $request->name,
                'has_image' => $request->filled('image'),
                'image_size' => $request->filled('image') ? strlen($request->input('image')) : 0
            ]);

            Mail::to($request->email)->send(new BirthdayGreetingMail(
                $request->name,
                $title,
                $messageText,
                $imageData,
                $imageMime
            ));

            \Log::info("Saludo de cumpleaños enviado a {$request->email} para {$request->name}");

            // Limpiar archivos temporales antiguos (más de 1 hora)
            $this->cleanupTempImages();

            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            \Log::error("Error al enviar saludo de cumpleaños: " . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'ok' => false, 
                'error' => $e->getMessage(),
                'details' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    public function updateBackgroundImage(Request $request)
    {
        $request->validate([
            'imagen' => 'required|image|max:4096',
        ]);

        $file = $request->file('imagen');

        $filename = 'Ima.Cumple_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('Backend/Style'), $filename);

        $ruta = 'Backend/Style/' . $filename;
        $hoy  = date('Y-m-d');

        DB::table('cumple_imagenes')->update(['activo' => 0]);

        DB::table('cumple_imagenes')->insert([
            'ruta_imagen'   => $ruta,
            'vigente_desde' => $hoy,
            'vigente_hasta' => null,
            'activo'        => 1,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return response()->json([
            'ok'          => true,
            'ruta_imagen' => $ruta,
        ]);
    }

    /**
     * Actualiza el texto del saludo (título y mensaje) asociado
     * a la imagen de cumpleaños vigente.
     */
    public function updateGreetingText(Request $request)
    {
        $data = $request->validate([
            'titulo'  => 'nullable|string|max:255',
            'mensaje' => 'nullable|string|max:1000',
        ]);

        $todayDate = date('Y-m-d');

        $imageConfig = DB::table('cumple_imagenes')
            ->where('activo', 1)
            ->where('vigente_desde', '<=', $todayDate)
            ->where(function ($q) use ($todayDate) {
                $q->whereNull('vigente_hasta')
                  ->orWhere('vigente_hasta', '>=', $todayDate);
            })
            ->orderBy('vigente_desde', 'desc')
            ->first();

        if ($imageConfig) {
            DB::table('cumple_imagenes')
                ->where('id', $imageConfig->id)
                ->update([
                    'titulo'     => $data['titulo'],
                    'mensaje'    => $data['mensaje'],
                    'updated_at' => now(),
                ]);

            $imageConfig = DB::table('cumple_imagenes')->where('id', $imageConfig->id)->first();
        } else {
            $id = DB::table('cumple_imagenes')->insertGetId([
                'ruta_imagen'   => 'Backend/Style/Ima.Cumple.jpg',
                'vigente_desde' => $todayDate,
                'vigente_hasta' => null,
                'activo'        => 1,
                'titulo'        => $data['titulo'],
                'mensaje'       => $data['mensaje'],
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            $imageConfig = DB::table('cumple_imagenes')->where('id', $id)->first();
        }

        return response()->json([
            'ok'      => true,
            'titulo'  => $imageConfig->titulo ?? null,
            'mensaje' => $imageConfig->mensaje ?? null,
        ]);
    }

    /**
     * Limpiar archivos temporales de imágenes de cumpleaños antiguos (más de 1 hora)
     */
    private function cleanupTempImages()
    {
        $tempDir = storage_path('app/temp');
        
        if (!is_dir($tempDir)) {
            return;
        }

        $files = glob($tempDir . '/birthday_*.{jpg,png,gif}', GLOB_BRACE);
        $oneHourAgo = time() - 3600;

        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $oneHourAgo) {
                @unlink($file);
            }
        }
    }
}

