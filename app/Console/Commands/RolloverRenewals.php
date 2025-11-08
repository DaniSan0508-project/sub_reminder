<?php

namespace App\Console\Commands;

use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RolloverRenewals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:rollover-renewals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atualiza o próximo ciclo de renovação para assinaturas que já venceram.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando verificação de "rollover" de assinaturas...');

        $yesterday = Carbon::yesterday()->toDateString();

        // 1. A CONSULTA MÁGICA
        // Busque assinaturas cuja data de renovação foi ontem ou antes.
        $subscriptionsToRollover = UserSubscription::query()
            ->where('renewal_date', '<=', $yesterday)
            ->get();

        if ($subscriptionsToRollover->isEmpty()) {
            $this->info('Nenhuma assinatura para atualizar o ciclo.');
            return;
        }

        $this->info("Encontradas " . $subscriptionsToRollover->count() . " assinaturas para 'rollover'.");

        foreach ($subscriptionsToRollover as $sub) {

            $nextRenewalDate = Carbon::parse($sub->renewal_date)->add(
                $sub->renewal_period_value,
                $sub->renewal_period_unit
            );


            $nextNotificationDate = $nextRenewalDate->copy()->sub(
                $sub->notify_before_value,
                $sub->notify_before_unit
            );

            $sub->update([
                'renewal_date' => $nextRenewalDate->toDateString(),
                'notification_date' => $nextNotificationDate->toDateString(),
                'last_notification_sent_at' => null,
            ]);
        }

        $this->info('Ciclos de assinatura atualizados.');
    }
}
