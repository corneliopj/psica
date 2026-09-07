<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('faturas', function (Blueprint $table) {
            $table->string('forma_pagamento')->nullable()->after('status');
            $table->string('transacao_id')->nullable()->after('forma_pagamento');
            $table->timestamp('pago_em')->nullable()->after('transacao_id');
        });
    }

    public function down(): void
    {
        Schema::table('faturas', function (Blueprint $table) {
            $table->dropColumn(['forma_pagamento', 'transacao_id', 'pago_em']);
        });
    }
};
