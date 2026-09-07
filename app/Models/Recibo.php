<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recibo extends Model
{
    protected $table = 'recibos';

    protected $fillable = [
        'fatura_id',
        'numero',
        'snapshot_fiscal',
        'texto_legal',
        'hash_autenticidade',
        'arquivo_pdf',
        'emitido_em',
    ];

    protected $casts = [
        'snapshot_fiscal' => 'array',
        'emitido_em' => 'datetime',
    ];

    public function fatura(): BelongsTo
    {
        return $this->belongsTo(Fatura::class, 'fatura_id');
    }
}
