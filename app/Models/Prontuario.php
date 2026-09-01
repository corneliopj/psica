<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;
use App\Models\Paciente;
use App\Models\User;

class Prontuario extends Model
{
    protected $fillable = [
        'agendamento_id',
        'paciente_id',
        'profissional_id',
        'title',
        'content',
        'anotacoes',
        'historico_clinico',
        'data_registro',
        'created_by',
    ];

    protected $casts = [
        'anotacoes' => 'encrypted',
        'historico_clinico' => 'encrypted',
        'data_registro' => 'datetime',
    ];

    public function paciente(): BelongsTo
    {
        $fk = Schema::hasColumn('prontuarios', 'paciente_id') ? 'paciente_id' : 'patient_id';
        return $this->belongsTo(Paciente::class, $fk);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
