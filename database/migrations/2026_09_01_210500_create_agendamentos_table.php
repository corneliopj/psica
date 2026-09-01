<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agendamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profissional_id')->constrained('profissionais')->cascadeOnDelete();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->dateTime('data_hora_inicio');
            $table->dateTime('data_hora_fim');
            $table->enum('status', ['solicitado', 'confirmado', 'cancelado', 'realizado', 'rejeitado'])->default('solicitado');
            $table->text('observacoes_cancelamento')->nullable();
            $table->timestamps();

            $table->index(['profissional_id', 'data_hora_inicio']);
            $table->index(['paciente_id', 'data_hora_inicio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agendamentos');
    }
};
