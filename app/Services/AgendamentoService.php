<?php

namespace App\Services;

use App\Contracts\AgendamentoServiceContract;
use App\Contracts\AuditoriaServiceContract;
use App\Contracts\FaturaServiceContract;
use App\Models\Agendamento;
use App\Models\Paciente;
use App\Models\Profissional;
use App\Models\Slot;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class AgendamentoService implements AgendamentoServiceContract
{
    public function __construct(
        protected AuditoriaServiceContract $auditoriaService,
        protected FaturaServiceContract $faturaService,
    ) {}

    public function solicitarAgendamento(int $pacienteId, int $profissionalId, string $inicio, ?string $fim = null, ?string $observacoes = null, ?float $valorSessao = null): Agendamento
    {
        $inicioDt = Carbon::parse($inicio);
        $fimDt = $fim ? Carbon::parse($fim) : $inicioDt->copy()->addMinutes(50);

        if ($fimDt->lessThanOrEqualTo($inicioDt)) {
            throw new InvalidArgumentException('A data de fim deve ser posterior à data de início.');
        }

        $paciente = Paciente::findOrFail($pacienteId);
        $profissional = Profissional::findOrFail($profissionalId);

        $agendamento = DB::transaction(function () use ($paciente, $profissional, $inicioDt, $fimDt, $observacoes, $valorSessao) {
            $this->bloquearSlotsDoProfissional($profissional, $inicioDt, $fimDt);

            $queryConflito = Agendamento::query()
                ->where('profissional_id', $profissional->id)
                ->whereNotIn('status', ['cancelado', 'rejeitado']);

            if (Agendamento::usesLegacySchedule()) {
                $queryConflito->where(Agendamento::startColumn(), $inicioDt->toDateTimeString());
            } else {
                $queryConflito
                    ->where(Agendamento::startColumn(), '<', $fimDt->toDateTimeString())
                    ->where(Agendamento::endColumn(), '>', $inicioDt->toDateTimeString());
            }

            $conflito = $queryConflito->lockForUpdate()->exists();

            if ($conflito) {
                throw new InvalidArgumentException('Esse horário já está ocupado ou bloqueado.');
            }

            $duracao = $inicioDt->diffInMinutes($fimDt);

            $item = Agendamento::create(Agendamento::makeSchedulingPayload(
                pacienteId: (int) $paciente->id,
                inicio: $inicioDt,
                duracaoMinutos: $duracao,
                status: 'solicitado',
                observacoes: $observacoes,
                profissionalId: (int) $profissional->id,
                valorSessao: $valorSessao ?? 0,
            ));

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

    public function confirmarAgendamento(int $agendamentoId, int $usuarioId, ?float $valorSessao = null): Agendamento
    {
        $usuario = Usuario::findOrFail($usuarioId);

        if (! in_array($usuario->perfil, ['admin', 'profissional'], true)) {
            throw new InvalidArgumentException('Usuário sem permissão para confirmar agendamento.');
        }

        $agendamento = DB::transaction(function () use ($agendamentoId, $usuarioId, $valorSessao) {
            $agendamento = Agendamento::query()->lockForUpdate()->findOrFail($agendamentoId);

            $this->assertStatusAtual($agendamento, ['solicitado']);
            $this->assertSemConflitoNoHorario($agendamento);

            $slot = $this->buscarSlotDoAgendamento($agendamento, true);

            if (! $slot instanceof Slot || $slot->status !== 'free') {
                throw new InvalidArgumentException('O slot do agendamento não está disponível para confirmação.');
            }

            $slot->status = 'occupied';
            $slot->save();

            if ($valorSessao !== null) {
                $agendamento->valor_sessao = $valorSessao;
            }

            $statusAntigo = $agendamento->status;
            $agendamento->status = 'confirmado';
            $agendamento->save();

            $this->faturaService->criarOuAtualizarPorAgendamento($agendamento);

            $this->auditoriaService->registrar('agendamentos', $agendamento->id, 'confirmado', $usuarioId, [
                'status_antigo' => $statusAntigo,
                'status_novo' => 'confirmado',
            ]);

            return $agendamento;
        });

        return $agendamento;
    }

    public function rejeitarAgendamento(int $agendamentoId, int $usuarioId, ?string $motivo = null): Agendamento
    {
        $usuario = Usuario::findOrFail($usuarioId);

        if (! in_array($usuario->perfil, ['admin', 'profissional'], true)) {
            throw new InvalidArgumentException('Usuário sem permissão para rejeitar agendamento.');
        }

        $motivo = trim((string) $motivo);
        if ($motivo === '') {
            throw new InvalidArgumentException('Motivo da rejeição é obrigatório.');
        }

        $agendamento = DB::transaction(function () use ($agendamentoId, $usuarioId, $motivo) {
            $agendamento = Agendamento::query()->lockForUpdate()->findOrFail($agendamentoId);

            $this->assertStatusAtual($agendamento, ['solicitado']);

            $agendamento->status = 'rejeitado';
            $agendamento->observacoes_cancelamento = $motivo;
            $agendamento->save();

            $this->liberarSlotDoAgendamento($agendamento);

            $this->auditoriaService->registrar('agendamentos', $agendamento->id, 'rejeitado', $usuarioId, [
                'motivo' => $motivo,
            ]);

            return $agendamento;
        });

        return $agendamento;
    }

    public function cancelarAgendamento(int $agendamentoId, int $usuarioId, ?string $motivo = null): Agendamento
    {
        $motivo = trim((string) $motivo);
        if ($motivo === '') {
            throw new InvalidArgumentException('Motivo do cancelamento é obrigatório.');
        }

        Usuario::findOrFail($usuarioId);

        $agendamento = DB::transaction(function () use ($agendamentoId, $usuarioId, $motivo) {
            $agendamento = Agendamento::query()->lockForUpdate()->findOrFail($agendamentoId);

            $this->assertStatusAtual($agendamento, ['solicitado', 'confirmado']);

            $agendamento->status = 'cancelado';
            $agendamento->observacoes_cancelamento = $motivo;
            $agendamento->save();

            $this->liberarSlotDoAgendamento($agendamento);

            $this->auditoriaService->registrar('agendamentos', $agendamento->id, 'cancelado', $usuarioId, [
                'motivo' => $motivo,
            ]);

            return $agendamento;
        });

        return $agendamento;
    }

    public function realizarAgendamento(int $agendamentoId, int $usuarioId): Agendamento
    {
        $usuario = Usuario::findOrFail($usuarioId);

        if (! in_array($usuario->perfil, ['admin', 'profissional'], true)) {
            throw new InvalidArgumentException('Usuário sem permissão para concluir agendamento.');
        }

        $agendamento = DB::transaction(function () use ($agendamentoId, $usuarioId) {
            $agendamento = Agendamento::query()->lockForUpdate()->findOrFail($agendamentoId);

            $this->assertStatusAtual($agendamento, ['confirmado']);

            $agendamento->status = 'realizado';
            $agendamento->save();

            $this->faturaService->criarOuAtualizarPorAgendamento($agendamento);

            $this->auditoriaService->registrar('agendamentos', $agendamento->id, 'realizado', $usuarioId, [
                'status_novo' => 'realizado',
            ]);

            return $agendamento;
        });

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

    protected function assertStatusAtual(Agendamento $agendamento, array $statusPermitidos): void
    {
        if (! in_array($agendamento->status, $statusPermitidos, true)) {
            throw new InvalidArgumentException('Transição de estado inválida para o agendamento.');
        }
    }

    protected function assertSemConflitoNoHorario(Agendamento $agendamento): void
    {
        $inicio = Carbon::parse($agendamento->scheduled_at)->toDateTimeString();
        $fim = Carbon::parse($agendamento->ends_at)->toDateTimeString();

        $queryConflito = Agendamento::query()
            ->where('profissional_id', $agendamento->profissional_id)
            ->where('id', '!=', $agendamento->id)
            ->whereIn('status', ['confirmado', 'realizado']);

        if (Agendamento::usesLegacySchedule()) {
            $queryConflito->where(Agendamento::startColumn(), $inicio);
        } else {
            $queryConflito
                ->where(Agendamento::startColumn(), '<', $fim)
                ->where(Agendamento::endColumn(), '>', $inicio);
        }

        $conflito = $queryConflito->lockForUpdate()->exists();

        if ($conflito) {
            throw new InvalidArgumentException('Já existe sessão confirmada para este horário.');
        }
    }

    protected function buscarSlotDoAgendamento(Agendamento $agendamento, bool $bloquear = false): ?Slot
    {
        $profissional = Profissional::find($agendamento->profissional_id);
        if (! $profissional instanceof Profissional || empty($profissional->usuario_id)) {
            return null;
        }

        $inicio = Carbon::parse($agendamento->scheduled_at);
        $fim = Carbon::parse($agendamento->ends_at);
        $slotUsuarioColumn = Schema::hasColumn('slots', 'usuario_id') ? 'usuario_id' : 'user_id';

        $query = Slot::query()
            ->where($slotUsuarioColumn, $profissional->usuario_id)
            ->where('start', '<=', $inicio->toDateTimeString())
            ->where('end', '>=', $fim->toDateTimeString())
            ->orderBy('start');

        if ($bloquear) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    protected function liberarSlotDoAgendamento(Agendamento $agendamento): void
    {
        $slot = $this->buscarSlotDoAgendamento($agendamento, true);
        if (! $slot instanceof Slot) {
            return;
        }

        $slot->status = 'free';
        $slot->save();
    }

    protected function bloquearSlotsDoProfissional(Profissional $profissional, Carbon $inicio, Carbon $fim): void
    {
        $slotUsuarioColumn = Schema::hasColumn('slots', 'usuario_id') ? 'usuario_id' : 'user_id';

        Slot::query()
            ->where($slotUsuarioColumn, $profissional->usuario_id)
            ->where('start', '<', $fim->toDateTimeString())
            ->where('end', '>', $inicio->toDateTimeString())
            ->lockForUpdate()
            ->get();
    }
}
