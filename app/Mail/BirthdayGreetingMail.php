<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class BirthdayGreetingMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $name;
    public string $title;
    public string $greetingMessage;

    /**
     * Imagen ya renderizada (background + texto) en binario.
     * Si es null, se usa la imagen de fondo por defecto.
     */
    public ?string $imageData;

    /**
     * Tipo MIME de la imagen (ej. image/png).
     */
    public string $imageMime;

    public function __construct(
        string $name,
        ?string $title = null,
        ?string $message = null,
        ?string $imageData = null,
        ?string $imageMime = null
    ) {
        $this->name = $name;
        $this->title = $title ?: '¡Feliz cumpleaños!';
        $this->greetingMessage = $message
            ?: 'El Colegio de Economistas de Lima le desea muchos éxitos y que tenga un gran día.';

        $this->imageData = $imageData;
        $this->imageMime = $imageMime ?: 'image/png';

        // Si no viene una imagen renderizada desde el navegador,
        // cargamos la imagen de fondo configurada en BD como fallback.
        if ($this->imageData === null) {
            $today = date('Y-m-d');
            $imageConfig = DB::table('cumple_imagenes')
                ->where('activo', 1)
                ->where('vigente_desde', '<=', $today)
                ->where(function ($q) use ($today) {
                    $q->whereNull('vigente_hasta')
                        ->orWhere('vigente_hasta', '>=', $today);
                })
                ->orderBy('vigente_desde', 'desc')
                ->first();

            $relativePath = $imageConfig ? $imageConfig->ruta_imagen : 'Backend/Style/Ima.Cumple.jpg';
            $absolutePath = public_path($relativePath);

            if (is_readable($absolutePath)) {
                $this->imageData = @file_get_contents($absolutePath) ?: null;

                $detectedMime = @mime_content_type($absolutePath);
                if (is_string($detectedMime) && $detectedMime !== '') {
                    $this->imageMime = $detectedMime;
                }
            }
        }
    }

    public function build()
    {
        $hasCustomImage = $this->imageData !== null;
        $tempImagePath = null;

        // Si hay imagen personalizada, guardarla temporalmente
        if ($hasCustomImage) {
            $extension = match($this->imageMime) {
                'image/jpeg', 'image/jpg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                default => 'jpg',
            };

            // Guardar en storage/app/temp
            $tempImagePath = storage_path('app/temp/birthday_' . uniqid() . '.' . $extension);
            
            // Asegurar que el directorio existe
            $tempDir = dirname($tempImagePath);
            if (!is_dir($tempDir)) {
                @mkdir($tempDir, 0755, true);
            }

            file_put_contents($tempImagePath, $this->imageData);
        }

        return $this->subject('Saludo de cumpleaños')
            ->view('emails.birthday_greeting')
            ->with([
                'name'            => $this->name,
                'title'           => $this->title,
                'greetingMessage' => $this->greetingMessage,
                'customImagePath' => $tempImagePath,
                'useCustomImage'  => $hasCustomImage,
            ]);
    }
}

