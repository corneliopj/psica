<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prontuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agendamento_id')->unique()->constrained('agendamentos')->cascadeOnDelete();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->foreignId('profissional_id')->constrained('profissionais')->cascadeOnDelete();
            $table->binary('anotacoes')->nullable();
            $table->binary('historico_clinico')->nullable();
            $table->dateTime('data_registro')->useCurrent();
            $table->timestamps();

            $table->index('paciente_id');
            $table->index('profissional_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prontuarios');
    }
};
