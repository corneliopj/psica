<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profissionais', function (Blueprint $table) {
            $table->text('cpf_cnpj')->nullable()->after('telefone');
        });

        Schema::table('pacientes', function (Blueprint $table) {
            $table->string('nome_responsavel')->nullable()->after('nome');
            $table->text('cpf_responsavel')->nullable()->after('nome_responsavel');
        });
    }

    public function down(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->dropColumn(['nome_responsavel', 'cpf_responsavel']);
        });

        Schema::table('profissionais', function (Blueprint $table) {
            $table->dropColumn('cpf_cnpj');
        });
    }
};
