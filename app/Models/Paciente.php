<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use App\Models\Prontuario;
use App\Models\Agendamento;

class Paciente extends Model
{
    /**
     * Dynamically set table to support both English and Portuguese schemas.
     */
    protected $table = null;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(Schema::hasTable('pacientes') ? 'pacientes' : 'patients');
    }

    protected $fillable = [
        'usuario_id',
        'name',
        'nome',
        'email',
        'phone',
        'telefone',
        'cpf',
        'birth_date',
        'data_nascimento',
        'notes',
        'contato_emergencia',
        'status',
    ];

    protected $casts = [
        'telefone' => 'encrypted',
        'cpf' => 'encrypted',
        'data_nascimento' => 'date',
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
