<?php

namespace App\Services;

use App\Contracts\AgendamentoServiceContract;
use App\Contracts\AuditoriaServiceContract;
use App\Models\Agendamento;
use App\Models\Paciente;
use App\Models\Profissional;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AgendamentoService implements AgendamentoServiceContract
{
    public function __construct(
        protected AuditoriaServiceContract $auditoriaService
    ) {}

    public function solicitarAgendamento(int $pacienteId, int $profissionalId, string $inicio, ?string $fim = null, ?string $observacoes = null): Agendamento
    {
        $inicioDt = Carbon::parse($inicio);
        $fimDt = $fim ? Carbon::parse($fim) : $inicioDt->copy()->addMinutes(50);

        if ($fimDt->lessThanOrEqualTo($inicioDt)) {
            throw new InvalidArgumentException('A data de fim deve ser posterior à data de início.');
        }

        $paciente = Paciente::findOrFail($pacienteId);
        $profissional = Profissional::findOrFail($profissionalId);

        if ($this->verificarSobreposicao($profissionalId, $inicioDt->toDateTimeString(), $fimDt->toDateTimeString())) {
            throw new InvalidArgumentException('Esse horário já está ocupado ou bloqueado.');
        }

        $agendamento = DB::transaction(function () use ($paciente, $profissional, $inicioDt, $fimDt, $observacoes) {
            $item = Agendamento::create([
                'profissional_id' => $profissional->id,
                'paciente_id' => $paciente->id,
                'data_hora_inicio' => $inicioDt->toDateTimeString(),
                'data_hora_fim' => $fimDt->toDateTimeString(),
                'status' => 'solicitado',
                'observacoes_cancelamento' => $observacoes,
            ]);

            $this->auditoriaService->registrar('agendamentos', $item->id, 'criado', $paciente->usuario_id ?? null, [
                'profissional_id' => $profissional->id,
                'paciente_id' => $paciente->id,
                'inicio' => $inicioDt->toDateTimeString(),
                'fim' => $fimDt->toDateTimeString(),
            ]);

            return $item;
        });

        return $agendamento;
    }

    public function confirmarAgendamento(int $agendamentoId, int $usuarioId): Agendamento
    {
        $agendamento = Agendamento::findOrFail($agendamentoId);
        $usuario = Usuario::findOrFail($usuarioId);

        if (! in_array($usuario->perfil, ['admin', 'profissional'], true)) {
            throw new InvalidArgumentException('Usuário sem permissão para confirmar agendamento.');
        }

        $agendamento->status = 'confirmado';
        $agendamento->save();

        $this->auditoriaService->registrar('agendamentos', $agendamento->id, 'confirmado', $usuarioId, [
            'status_antigo' => 'solicitado',
            'status_novo' => 'confirmado',
        ]);

        return $agendamento;
    }

    public function rejeitarAgendamento(int $agendamentoId, int $usuarioId, ?string $motivo = null): Agendamento
    {
        $agendamento = Agendamento::findOrFail($agendamentoId);
        $usuario = Usuario::findOrFail($usuarioId);

        if (! in_array($usuario->perfil, ['admin', 'profissional'], true)) {
            throw new InvalidArgumentException('Usuário sem permissão para rejeitar agendamento.');
        }

        $agendamento->status = 'rejeitado';
        $agendamento->observacoes_cancelamento = $motivo;
        $agendamento->save();

        $this->auditoriaService->registrar('agendamentos', $agendamento->id, 'rejeitado', $usuarioId, [
            'motivo' => $motivo,
        ]);

        return $agendamento;
    }

    public function cancelarAgendamento(int $agendamentoId, int $usuarioId, ?string $motivo = null): Agendamento
    {
        $agendamento = Agendamento::findOrFail($agendamentoId);
        $usuario = Usuario::findOrFail($usuarioId);

        $agendamento->status = 'cancelado';
        $agendamento->observacoes_cancelamento = $motivo;
        $agendamento->save();

        $this->auditoriaService->registrar('agendamentos', $agendamento->id, 'cancelado', $usuarioId, [
            'motivo' => $motivo,
        ]);

        return $agendamento;
    }

    public function reagendarAgendamento(int $agendamentoId, int $usuarioId, string $novaDataHoraInicio): Agendamento
    {
        $agendamento = Agendamento::findOrFail($agendamentoId);
        $usuario = Usuario::findOrFail($usuarioId);

        $novaInicio = Carbon::parse($novaDataHoraInicio);
        $duracao = $agendamento->data_hora_fim->diffInMinutes($agendamento->data_hora_inicio);
        $novaFim = $novaInicio->copy()->addMinutes($duracao);

        if ($this->verificarSobreposicao($agendamento->profissional_id, $novaInicio->toDateTimeString(), $novaFim->toDateTimeString(), $agendamentoId)) {
            throw new InvalidArgumentException('O novo horário para reagendamento está indisponível.');
        }

        $agendamento->data_hora_inicio = $novaInicio;
        $agendamento->data_hora_fim = $novaFim;
        $agendamento->status = 'confirmado';
        $agendamento->save();

        $this->auditoriaService->registrar('agendamentos', $agendamento->id, 'reagendado', $usuarioId, [
            'nova_data_hora_inicio' => $novaInicio->toDateTimeString(),
            'nova_data_hora_fim' => $novaFim->toDateTimeString(),
        ]);

        return $agendamento;
    }

    public function verificarSobreposicao(int $profissionalId, string $inicio, string $fim, ?int $ignorarAgendamentoId = null): bool
    {
        $inicioDt = Carbon::parse($inicio);
        $fimDt = Carbon::parse($fim);

        $query = Agendamento::query()
            ->where('profissional_id', $profissionalId)
            ->where('status', '!=', 'cancelado')
            ->where('status', '!=', 'rejeitado')
            ->where('data_hora_inicio', '<', $fimDt)
            ->where('data_hora_fim', '>', $inicioDt);

        if ($ignorarAgendamentoId) {
            $query->where('id', '!=', $ignorarAgendamentoId);
        }

        return $query->exists();
    }
}
