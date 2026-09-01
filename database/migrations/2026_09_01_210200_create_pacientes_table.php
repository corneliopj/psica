<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('nome');
            $table->binary('telefone')->nullable();
            $table->binary('cpf')->nullable();
            $table->date('data_nascimento')->nullable();
            $table->string('contato_emergencia')->nullable();
            $table->enum('status', ['ativo', 'suspenso', 'alta'])->default('ativo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
