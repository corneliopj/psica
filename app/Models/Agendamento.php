<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;
use App\Models\Paciente;
use App\Models\Profissional;

class Agendamento extends Model
{
    protected $appends = [
        'scheduled_at',
        'duration_minutes',
        'notes',
        'ends_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'data_hora_inicio' => 'datetime',
        'data_hora_fim' => 'datetime',
    ];

    protected $fillable = [
        'profissional_id',
        'paciente_id',
        'data_hora_inicio',
        'data_hora_fim',
        'scheduled_at',
        'duration_minutes',
        'status',
        'notes',
        'observacoes_cancelamento',
    ];

    public static function startColumn(): string
    {
        return Schema::hasColumn('agendamentos', 'scheduled_at') ? 'scheduled_at' : 'data_hora_inicio';
    }

    public static function endColumn(): ?string
    {
        return Schema::hasColumn('agendamentos', 'data_hora_fim') ? 'data_hora_fim' : null;
    }

    public static function usesLegacySchedule(): bool
    {
        return self::startColumn() === 'scheduled_at';
    }

    public static function makeSchedulingPayload(
        int $pacienteId,
        Carbon $inicio,
        int $duracaoMinutos = 60,
        string $status = 'solicitado',
        ?string $observacoes = null,
        ?int $profissionalId = null,
    ): array {
        if (self::usesLegacySchedule()) {
            return array_filter([
                'profissional_id' => $profissionalId,
                'paciente_id' => $pacienteId,
                'scheduled_at' => $inicio->toDateTimeString(),
                'duration_minutes' => $duracaoMinutos,
                'status' => $status === 'solicitado' ? 'scheduled' : $status,
                'notes' => $observacoes,
            ], static fn ($value) => $value !== null);
        }

        return array_filter([
            'profissional_id' => $profissionalId,
            'paciente_id' => $pacienteId,
            'data_hora_inicio' => $inicio->toDateTimeString(),
            'data_hora_fim' => $inicio->copy()->addMinutes($duracaoMinutos)->toDateTimeString(),
            'status' => $status === 'scheduled' ? 'solicitado' : $status,
            'observacoes_cancelamento' => $observacoes,
        ], static fn ($value) => $value !== null);
    }

    public function getScheduledAtAttribute($value): ?string
    {
        if ($value) {
            return $value;
        }

        return isset($this->attributes['data_hora_inicio']) ? (string) $this->attributes['data_hora_inicio'] : null;
    }

    public function getDurationMinutesAttribute($value): int
    {
        if ($value !== null) {
            return (int) $value;
        }

        if (! empty($this->attributes['data_hora_inicio']) && ! empty($this->attributes['data_hora_fim'])) {
            return Carbon::parse($this->attributes['data_hora_inicio'])
                ->diffInMinutes(Carbon::parse($this->attributes['data_hora_fim']));
        }

        return 60;
    }

    public function getNotesAttribute($value): ?string
    {
        if ($value !== null) {
            return $value;
        }

        return $this->attributes['observacoes_cancelamento'] ?? null;
    }

    public function getEndsAtAttribute(): ?string
    {
        if (! empty($this->attributes['data_hora_fim'])) {
            return (string) $this->attributes['data_hora_fim'];
        }

        $scheduledAt = $this->scheduled_at;

        if (! $scheduledAt) {
            return null;
        }

        return Carbon::parse($scheduledAt)->addMinutes($this->duration_minutes)->toDateTimeString();
    }

    public function paciente(): BelongsTo
    {
        $fk = Schema::hasColumn('agendamentos', 'paciente_id') ? 'paciente_id' : 'patient_id';
        return $this->belongsTo(Paciente::class, $fk);
    }

    public function profissional(): BelongsTo
    {
        return $this->belongsTo(Profissional::class, 'profissional_id');
    }
}
