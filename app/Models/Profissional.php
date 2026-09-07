<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profissional extends Model
{
    protected $table = 'profissionais';

    protected $fillable = [
        'usuario_id',
        'nome',
        'especialidade',
        'telefone',
        'cpf_cnpj',
        'status',
        'observacoes',
    ];

    protected $casts = [
        'telefone' => 'encrypted',
        'cpf_cnpj' => 'encrypted',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function agendamentos(): HasMany
    {
        return $this->hasMany(Agendamento::class, 'profissional_id');
    }

    public function disponibilidades(): HasMany
    {
        return $this->hasMany(Disponibilidade::class, 'profissional_id');
    }

    public function faturas(): HasMany
    {
        return $this->hasMany(Fatura::class, 'profissional_id');
    }
}
