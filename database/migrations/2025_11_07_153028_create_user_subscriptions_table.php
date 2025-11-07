<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');

            $table->decimal('price', 8, 2);
            $table->string('currency', 3)->default('BRL');

            // O ciclo de renovação
            $table->date('renewal_date'); // A *próxima* data de cobrança
            $table->integer('renewal_period_value'); // Ex: 1
            $table->enum('renewal_period_unit', ['day', 'month', 'year']); // Ex: 'month'

            // Preferência de notificação (para referência)
            $table->integer('notify_before_value'); // Ex: 7
            $table->enum('notify_before_unit', ['day', 'month']); // Ex: 'day'

            // O CAMPO CHAVE: A data exata para enviar o aviso
            $table->date('notification_date');

            // Controle para não enviar spam
            $table->timestamp('last_notification_sent_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};
