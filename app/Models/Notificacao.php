<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notificacao extends Model
{
    protected $table = 'notificacoes';

    protected $fillable = [
        'usuario_id',
        'tipo',
        'canal',
        'assunto',
        'mensagem',
        'status',
        'payload',
        'enviado_em',
    ];

    protected $casts = [
        'payload' => 'array',
        'enviado_em' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
