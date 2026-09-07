<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Fatura extends Model
{
    protected $table = 'faturas';

    protected $fillable = [
        'agendamento_id',
        'paciente_id',
        'profissional_id',
        'valor',
        'status',
        'forma_pagamento',
        'transacao_id',
        'pago_em',
        'numero_recibo',
        'emitida_em',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'pago_em' => 'datetime',
        'emitida_em' => 'datetime',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function agendamento(): BelongsTo
    {
        return $this->belongsTo(Agendamento::class, 'agendamento_id');
    }

    public function profissional(): BelongsTo
    {
        return $this->belongsTo(Profissional::class, 'profissional_id');
    }

    public function recibo(): HasOne
    {
        return $this->hasOne(Recibo::class, 'fatura_id');
    }
}