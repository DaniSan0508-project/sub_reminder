<?php

namespace App\Mail;

use App\Models\UserSubscription; // <-- Importe o Model
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RenewalReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Crie uma nova instância da mensagem.
     * Nós recebemos a assinatura e a tornamos pública
     * para que a View (Blade) possa acessá-la.
     */
    public function __construct(
        public UserSubscription $subscription
    ) {}

    /**
     * Define o "envelope" (Assunto, De, Para).
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Lembrete de Renovação de Assinatura',
        );
    }

    /**
     * Obtém a definição de conteúdo da mensagem.
     * É aqui que apontamos para o nosso arquivo Blade.
     */
    public function content(): Content
    {
        return new Content(
            // O arquivo estará em: resources/views/emails/renewal_reminder.blade.php
            view: 'emails.renewal_reminder',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
