<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prontuarios', function (Blueprint $table) {
            $table->boolean('selado')->default(false)->after('data_registro');
            $table->dateTime('data_selamento')->nullable()->after('selado');
            $table->string('hash_integridade', 64)->nullable()->after('data_selamento');
        });
    }

    public function down(): void
    {
        Schema::table('prontuarios', function (Blueprint $table) {
            $table->dropColumn(['selado', 'data_selamento', 'hash_integridade']);
        });
    }
};
