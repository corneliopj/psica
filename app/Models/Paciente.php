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

    protected function resolveColumn(string $pt, string $en): string
    {
        return Schema::hasColumn($this->getTable(), $pt) ? $pt : $en;
    }

    public function getNameAttribute(): ?string
    {
        $column = $this->resolveColumn('nome', 'name');
        return $this->getAttributeValue($column);
    }

    public function setNameAttribute(?string $value): void
    {
        $column = $this->resolveColumn('nome', 'name');
        $this->attributes[$column] = $value;
    }

    public function getPhoneAttribute(): ?string
    {
        $column = $this->resolveColumn('telefone', 'phone');
        return $this->getAttributeValue($column);
    }

    public function setPhoneAttribute(?string $value): void
    {
        $column = $this->resolveColumn('telefone', 'phone');
        $this->attributes[$column] = $value;
    }

    public function getBirthDateAttribute(): mixed
    {
        $column = $this->resolveColumn('data_nascimento', 'birth_date');
        return $this->getAttributeValue($column);
    }

    public function setBirthDateAttribute(mixed $value): void
    {
        $column = $this->resolveColumn('data_nascimento', 'birth_date');
        $this->attributes[$column] = $value;
    }

    public function getNotesAttribute(): ?string
    {
        $column = Schema::hasColumn($this->getTable(), 'notes') ? 'notes' : 'contato_emergencia';
        return $this->getAttributeValue($column);
    }

    public function setNotesAttribute(?string $value): void
    {
        $column = Schema::hasColumn($this->getTable(), 'notes') ? 'notes' : 'contato_emergencia';
        $this->attributes[$column] = $value;
    }

    public function setEmailAttribute(?string $value): void
    {
        if (Schema::hasColumn($this->getTable(), 'email')) {
            $this->attributes['email'] = $value;
        }
    }

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
