<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recibos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fatura_id')->unique()->constrained('faturas')->cascadeOnDelete();
            $table->string('numero')->unique();
            $table->json('snapshot_fiscal');
            $table->text('texto_legal');
            $table->string('hash_autenticidade', 64);
            $table->string('arquivo_pdf')->nullable();
            $table->timestamp('emitido_em')->useCurrent();
            $table->timestamps();

            $table->index('numero');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recibos');
    }
};
