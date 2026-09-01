<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agendamento_id')->constrained('agendamentos')->cascadeOnDelete();
            $table->foreignId('paciente_id')->constrained('pacientes')->cascadeOnDelete();
            $table->foreignId('profissional_id')->constrained('profissionais')->cascadeOnDelete();
            $table->decimal('valor', 10, 2)->default(0.00);
            $table->enum('status', ['pendente', 'pago', 'isento'])->default('pendente');
            $table->string('numero_recibo')->nullable();
            $table->timestamp('emitida_em')->nullable();
            $table->timestamps();

            $table->index('paciente_id');
            $table->index('agendamento_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faturas');
    }
};
