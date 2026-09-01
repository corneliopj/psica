<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disponibilidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profissional_id')->constrained('profissionais')->cascadeOnDelete();
            $table->tinyInteger('dia_semana')->comment('0=domingo, 1=segunda, ..., 6=sabado');
            $table->time('hora_inicio');
            $table->time('hora_fim');
            $table->unsignedInteger('duracao_sessao')->default(50);
            $table->unsignedInteger('intervalo_entre_sessoes')->default(10);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disponibilidades');
    }
};
