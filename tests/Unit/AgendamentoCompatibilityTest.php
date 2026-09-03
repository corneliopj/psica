<?php

namespace Tests\Unit;

use App\Models\Agendamento;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AgendamentoCompatibilityTest extends TestCase
{
    public function test_make_scheduling_payload_uses_current_schema_columns(): void
    {
        Schema::shouldReceive('hasColumn')
            ->once()
            ->with('agendamentos', 'scheduled_at')
            ->andReturnFalse();

        $inicio = Carbon::parse('2026-09-03 14:00:00');

        $payload = Agendamento::makeSchedulingPayload(
            pacienteId: 7,
            inicio: $inicio,
            duracaoMinutos: 90,
            status: 'scheduled',
            observacoes: 'Sessao inicial',
        );

        $this->assertSame('2026-09-03 14:00:00', $payload['data_hora_inicio']);
        $this->assertSame('2026-09-03 15:30:00', $payload['data_hora_fim']);
        $this->assertSame('solicitado', $payload['status']);
        $this->assertSame('Sessao inicial', $payload['observacoes_cancelamento']);
        $this->assertArrayNotHasKey('scheduled_at', $payload);
    }

    public function test_make_scheduling_payload_uses_legacy_columns_when_available(): void
    {
        Schema::shouldReceive('hasColumn')
            ->once()
            ->with('agendamentos', 'scheduled_at')
            ->andReturnTrue();

        $inicio = Carbon::parse('2026-09-03 14:00:00');

        $payload = Agendamento::makeSchedulingPayload(
            pacienteId: 7,
            inicio: $inicio,
            duracaoMinutos: 45,
            status: 'solicitado',
            observacoes: 'Sessao inicial',
        );

        $this->assertSame('2026-09-03 14:00:00', $payload['scheduled_at']);
        $this->assertSame(45, $payload['duration_minutes']);
        $this->assertSame('scheduled', $payload['status']);
        $this->assertSame('Sessao inicial', $payload['notes']);
        $this->assertArrayNotHasKey('data_hora_inicio', $payload);
    }

    public function test_accessors_expose_current_schema_fields_with_legacy_names(): void
    {
        $agendamento = new Agendamento([
            'data_hora_inicio' => '2026-09-03 14:00:00',
            'data_hora_fim' => '2026-09-03 15:00:00',
            'observacoes_cancelamento' => 'Observacao geral',
        ]);

        $this->assertSame('2026-09-03 14:00:00', $agendamento->scheduled_at);
        $this->assertSame(60, $agendamento->duration_minutes);
        $this->assertSame('Observacao geral', $agendamento->notes);
        $this->assertSame('2026-09-03 15:00:00', $agendamento->ends_at);

        $serialized = $agendamento->toArray();

        $this->assertSame('2026-09-03 14:00:00', $serialized['scheduled_at']);
        $this->assertSame('2026-09-03 15:00:00', $serialized['ends_at']);
    }
}