<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->enum('tipo', ['email', 'whatsapp', 'sms']);
            $table->enum('canal', ['agendamento', 'lembrente', 'cancelamento', 'confirmacao', 'reagendamento']);
            $table->string('assunto')->nullable();
            $table->text('mensagem');
            $table->enum('status', ['pendente', 'enviada', 'falhou'])->default('pendente');
            $table->json('payload')->nullable();
            $table->timestamp('enviado_em')->nullable();
            $table->timestamps();

            $table->index('usuario_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificacoes');
    }
};
