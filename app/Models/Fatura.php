<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fatura extends Model
{
    protected $table = 'faturas';

    protected $fillable = [
        'agendamento_id',
        'paciente_id',
        'profissional_id',
        'valor',
        'status',
        'numero_recibo',
        'emitida_em',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
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
}