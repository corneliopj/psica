<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use App\Models\Prontuario;
use App\Models\Agendamento;

class Paciente extends Model
{
    protected $table = 'patients';
    protected $fillable = [
        'name',
        'email',
        'phone',
        'birth_date',
        'notes',
    ];

    public function prontuarios(): HasMany
    {
        // suportar ambas as colunas: paciente_id (futuro) e patient_id (atual)
        $fk = Schema::hasColumn('prontuarios', 'paciente_id') ? 'paciente_id' : 'patient_id';
        return $this->hasMany(Prontuario::class, $fk);
    }

    public function agendamentos(): HasMany
    {
        $fk = Schema::hasColumn('agendamentos', 'paciente_id') ? 'paciente_id' : 'patient_id';
        return $this->hasMany(Agendamento::class, $fk);
    }
}
