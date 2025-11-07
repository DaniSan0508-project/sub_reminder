<?php

namespace App\Console\Commands;

use App\Models\UserSubscription;
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

        $this->info("Encontradas " . $subscriptionsToNotify->count() . " notificações para enviar.");

        foreach ($subscriptionsToNotify as $subscription) {

            FacadesLog::info('[NOTIFICAÇÃO] Enviando aviso para User ID: ' . $subscription->user_id .
                ' sobre a assinatura ID: ' . $subscription->id);


            $subscription->update([
                'last_notification_sent_at' => now()
            ]);
        }

        $this->info('Verificação concluída.');
    }
}
