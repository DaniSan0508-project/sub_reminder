<?php

namespace App\Console\Commands;

use App\Models\UserSubscription;
use App\Jobs\SendRenewalNotificationJob;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Container\Attributes\Log;
use Illuminate\Support\Facades\Log as FacadesLog;

class SendRenewalNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-renewals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica o banco por assinaturas que precisam de notificação hoje.';

    /**
     * Execute the console command.
     */
    /**
     * Execute o console command.
     */
    public function handle()
    {
        $this->info('Iniciando verificação de notificações...');

        $today = Carbon::today()->toDateString();

        $subscriptionsToNotify = UserSubscription::query()
            ->where('notification_date', $today)
            ->whereNull('last_notification_sent_at')
            ->get();

        if ($subscriptionsToNotify->isEmpty()) {
            $this->info('Nenhuma notificação para enviar hoje.');
            return;
        }

        $this->info("Encontradas " . $subscriptionsToNotify->count() . " notificações para despachar.");

        foreach ($subscriptionsToNotify as $subscription) {

            // 1. DESPACHA O JOB PARA A FILA
            // Em vez de fazer o trabalho aqui, nós o "despachamos".
            // O contêiner 'worker' vai pegar isso do 'redis' e executar.
            SendRenewalNotificationJob::dispatch($subscription);

            $this->info("Despachado Job para User ID: " . $subscription->user_id);

            // 2. REMOVEMOS A LÓGICA DE 'update' DAQUI
            // O próprio Job será responsável por marcar como "enviado".
        }

        $this->info('Verificação concluída. Jobs despachados.');
    }
}
