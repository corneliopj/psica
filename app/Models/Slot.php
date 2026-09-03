<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class Slot extends Model
{
    use HasFactory;

    protected $fillable = [
        'start', 'end', 'status', 'user_id', 'usuario_id', 'recurrence_rule'
    ];

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        $fk = Schema::hasColumn('slots', 'usuario_id') ? 'usuario_id' : 'user_id';
        return $this->belongsTo(Usuario::class, $fk);
    }

    public function user(): BelongsTo
    {
        return $this->usuario();
    }
}
