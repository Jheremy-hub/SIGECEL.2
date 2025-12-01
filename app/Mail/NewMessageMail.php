<?php

namespace App\Mail;

use App\Models\UserMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public UserMessage $messageModel;

    public function __construct(UserMessage $message)
    {
        // Cargamos también logs para poder obtener urgencia y estado
        $this->messageModel = $message->loadMissing(['sender', 'receiver', 'logs']);
        $this->subject($this->messageModel->subject ?: 'Nuevo documento en SIGE');
    }

    public function build()
    {
        // Determinar dirección From con tolerancia a .env no cargado
        $fromAddress = config('mail.from.address') ?: env('MAIL_FROM_ADDRESS') ?: env('MAIL_USERNAME');
        $fromName    = config('mail.from.name') ?: env('MAIL_FROM_NAME') ?: config('app.name', 'SIGECEL');

        // Garantizar cabecera From válida
        if (empty($fromAddress)) {
            $fromAddress = 'no-reply@sige.local';
        }

        $mailable = $this->from($fromAddress, $fromName)
            ->view('emails.new_message', [
                // Nota: no usar la variable 'message' en la vista porque Laravel la reserva
                'userMessage' => $this->messageModel,
                'url'         => url('messages/'.$this->messageModel->id),
            ]);

        // Permitir que el receptor responda al remitente real
        $senderEmail = optional($this->messageModel->sender)->email;
        if (!empty($senderEmail)) {
            $mailable->replyTo($senderEmail);
        }

        return $mailable;
    }
}
