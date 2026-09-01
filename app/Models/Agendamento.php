<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;
use App\Models\Paciente;

class Agendamento extends Model
{
    protected $fillable = [
        'profissional_id',
        'paciente_id',
        'data_hora_inicio',
        'data_hora_fim',
        'scheduled_at',
        'duration_minutes',
        'status',
        'notes',
        'observacoes_cancelamento',
    ];

    public function paciente(): BelongsTo
    {
        $fk = Schema::hasColumn('agendamentos', 'paciente_id') ? 'paciente_id' : 'patient_id';
        return $this->belongsTo(Paciente::class, $fk);
    }
}
