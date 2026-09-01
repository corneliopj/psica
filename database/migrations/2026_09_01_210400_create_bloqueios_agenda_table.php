<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bloqueios_agenda', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profissional_id')->constrained('profissionais')->cascadeOnDelete();
            $table->dateTime('data_inicio');
            $table->dateTime('data_fim');
            $table->string('motivo')->nullable();
            $table->enum('tipo', ['feriado', 'ferias', 'compromisso', 'outro'])->default('outro');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bloqueios_agenda');
    }
};
