<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;
use LogicException;
use App\Models\Paciente;
use App\Models\Usuario;

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
        'selado',
        'data_selamento',
        'hash_integridade',
        'created_by',
    ];

    protected $casts = [
        'anotacoes' => 'encrypted',
        'historico_clinico' => 'encrypted',
        'data_registro' => 'datetime',
        'data_selamento' => 'datetime',
        'selado' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::updating(function (Prontuario $prontuario) {
            if ($prontuario->getOriginal('selado')) {
                throw new LogicException('Prontuário selado não pode ser alterado.');
            }
        });

        static::deleting(function (Prontuario $prontuario) {
            if ($prontuario->selado) {
                throw new LogicException('Prontuário selado não pode ser removido.');
            }
        });
    }

    public function paciente(): BelongsTo
    {
        $fk = Schema::hasColumn('prontuarios', 'paciente_id') ? 'paciente_id' : 'patient_id';
        return $this->belongsTo(Paciente::class, $fk);
    }

    public function criador(): BelongsTo
    {
        $fk = Schema::hasColumn('prontuarios', 'usuario_id') ? 'usuario_id' : 'created_by';
        return $this->belongsTo(Usuario::class, $fk);
    }

    public function creator(): BelongsTo
    {
        return $this->criador();
    }
}
