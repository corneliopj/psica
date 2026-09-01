<?php

namespace App\Contracts;

use App\Models\Agendamento;

interface AgendamentoServiceContract
{
    public function solicitarAgendamento(int $pacienteId, int $profissionalId, string $inicio, ?string $fim = null, ?string $observacoes = null): Agendamento;

    public function confirmarAgendamento(int $agendamentoId, int $usuarioId): Agendamento;

    public function rejeitarAgendamento(int $agendamentoId, int $usuarioId, ?string $motivo = null): Agendamento;

    public function cancelarAgendamento(int $agendamentoId, int $usuarioId, ?string $motivo = null): Agendamento;

    public function reagendarAgendamento(int $agendamentoId, int $usuarioId, string $novaDataHoraInicio): Agendamento;

    public function verificarSobreposicao(int $profissionalId, string $inicio, string $fim, ?int $ignorarAgendamentoId = null): bool;
}
