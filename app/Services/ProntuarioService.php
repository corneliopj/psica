<?php

namespace App\Services;

use App\Contracts\ProntuarioServiceContract;
use App\Contracts\AuditoriaServiceContract;
use App\Models\Agendamento;
use App\Models\Prontuario;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;

class ProntuarioService implements ProntuarioServiceContract
{
    public function __construct(
        protected AuditoriaServiceContract $auditoriaService
    ) {}

    public function criarProntuario(int $agendamentoId, int $pacienteId, int $profissionalId, ?string $anotacoes = null, ?string $historicoClinico = null): Prontuario
    {
        $agendamento = Agendamento::findOrFail($agendamentoId);

        if ($agendamento->paciente_id !== $pacienteId || $agendamento->profissional_id !== $profissionalId) {
            throw new InvalidArgumentException('O prontuário não pertence ao paciente e profissional informados.');
        }

        $prontuario = Prontuario::create([
            'agendamento_id' => $agendamento->id,
            'paciente_id' => $pacienteId,
            'profissional_id' => $profissionalId,
            'anotacoes' => $anotacoes,
            'historico_clinico' => $historicoClinico,
            'data_registro' => now(),
        ]);

        $this->auditoriaService->registrar('prontuarios', $prontuario->id, 'criado', Auth::id(), [
            'agendamento_id' => $agendamentoId,
            'paciente_id' => $pacienteId,
        ]);

        return $prontuario;
    }

    public function atualizarProntuario(int $prontuarioId, int $usuarioId, array $dados): Prontuario
    {
        $prontuario = Prontuario::findOrFail($prontuarioId);

        if ($prontuario->selado) {
            throw new LogicException('Prontuário selado não pode ser atualizado.');
        }

        $original = $prontuario->toArray();
        $prontuario->fill($dados);
        $prontuario->save();

        $this->auditoriaService->registrar('prontuarios', $prontuario->id, 'atualizado', $usuarioId, [
            'antes' => $original,
            'depois' => $prontuario->fresh()->toArray(),
        ]);

        return $prontuario;
    }

    public function registrarDiarioSessao(int $prontuarioId, int $usuarioId, string $anotacoes, ?string $historicoClinico = null): Prontuario
    {
        $usuario = Usuario::findOrFail($usuarioId);
        $prontuario = Prontuario::findOrFail($prontuarioId);

        if (! in_array($usuario->perfil, ['admin', 'profissional'], true)) {
            throw new InvalidArgumentException('Usuário sem permissão para registrar diário clínico.');
        }

        if ($prontuario->selado) {
            throw new LogicException('Prontuário selado não pode receber novas anotações.');
        }

        $prontuario->anotacoes = $anotacoes;
        if ($historicoClinico !== null) {
            $prontuario->historico_clinico = $historicoClinico;
        }
        $prontuario->data_registro = now();
        $prontuario->save();

        $this->auditoriaService->registrar('prontuarios', $prontuario->id, 'diario_registrado', $usuarioId, [
            'agendamento_id' => $prontuario->agendamento_id,
        ]);

        return $prontuario;
    }

    public function selarRegistroSessao(int $prontuarioId, int $usuarioId): Prontuario
    {
        $usuario = Usuario::findOrFail($usuarioId);

        if (! in_array($usuario->perfil, ['admin', 'profissional'], true)) {
            throw new InvalidArgumentException('Usuário sem permissão para selar prontuário.');
        }

        return DB::transaction(function () use ($prontuarioId, $usuarioId) {
            $prontuario = Prontuario::query()->lockForUpdate()->findOrFail($prontuarioId);

            if ($prontuario->selado) {
                return $prontuario;
            }

            $agendamento = Agendamento::findOrFail($prontuario->agendamento_id);
            $hashBase = implode('|', [
                (string) $prontuario->id,
                (string) $prontuario->agendamento_id,
                (string) $prontuario->paciente_id,
                (string) $prontuario->profissional_id,
                (string) $prontuario->anotacoes,
                (string) $prontuario->historico_clinico,
                (string) Carbon::parse($agendamento->scheduled_at)->toIso8601String(),
                (string) Carbon::parse($agendamento->ends_at)->toIso8601String(),
            ]);

            $prontuario->hash_integridade = hash('sha256', $hashBase);
            $prontuario->selado = true;
            $prontuario->data_selamento = now();
            $prontuario->save();

            $this->auditoriaService->registrar('prontuarios', $prontuario->id, 'selado', $usuarioId, [
                'hash_integridade' => $prontuario->hash_integridade,
            ]);

            return $prontuario;
        });
    }

    public function visualizarProntuario(int $prontuarioId, int $usuarioId): Prontuario
    {
        $prontuario = Prontuario::findOrFail($prontuarioId);

        $this->auditoriaService->registrar('prontuarios', $prontuario->id, 'visualizado', $usuarioId, [
            'paciente_id' => $prontuario->paciente_id,
            'profissional_id' => $prontuario->profissional_id,
        ]);

        return $prontuario;
    }
}
