<?php

namespace App\Jobs;

use App\Models\UserSubscription;
use App\Mail\RenewalReminderMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendRenewalNotificationJob implements ShouldQueue // <-- Diz ao Laravel "jogue-me na fila"
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Crie uma nova instância do job.
     * Nós "injetamos" a assinatura que precisa de aviso.
     */
    public function __construct(
        public UserSubscription $subscription
    ) {}

    /**
     * Execute o job.
     */
    public function handle(): void
    {
        // 1. Obtenha o usuário dono da assinatura
        $user = $this->subscription->user;

        // Obtenha o serviço para termos o nome (ex: "Netflix")
        // Precisamos adicionar a Relação "service()" no Model
        $serviceName = $this->subscription->service->name ?? 'seu serviço';

        Log::info("[JOB INICIADO] Enviando notificação para: " . $user->email);

        // 2. ENVIAR O E-MAIL
        // (Isso requer que você configure um driver de e-mail como Mailtrap ou Mailgun no .env)
        try {
            Mail::to($user->email)->send(new RenewalReminderMail($this->subscription));
            Log::info("[JOB SUCESSO] E-mail enviado para: " . $user->email);
        } catch (\Exception $e) {
            Log::error("[JOB FALHA] Falha ao enviar e-mail: " . $e->getMessage());
        }

        // 3. ENVIAR A NOTIFICAÇÃO PUSH (Exemplo futuro)
        // if ($user->fcm_token) {
        //     Firebase::sendPush($user->fcm_token, 
        //         'Aviso de Renovação!',
        //         "Sua assinatura {$serviceName} será renovada em breve."
        //     );
        // }

        // 4. ATUALIZAR O CONTROLE DE SPAM
        // Movemos a lógica do Comando para o Job
        $this->subscription->update([
            'last_notification_sent_at' => now()
        ]);
    }
}
