<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Auditoria extends Model
{
    protected $table = 'auditorias';

    protected $fillable = [
        'usuario_id',
        'entidade',
        'entidade_id',
        'acao',
        'campos_alterados',
        'valor_antigo',
        'valor_novo',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'campos_alterados' => 'array',
        'valor_antigo' => 'array',
        'valor_novo' => 'array',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
