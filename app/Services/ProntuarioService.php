<?php

namespace App\Services;

use App\Contracts\ProntuarioServiceContract;
use App\Contracts\AuditoriaServiceContract;
use App\Models\Agendamento;
use App\Models\Prontuario;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

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

        $original = $prontuario->toArray();
        $prontuario->fill($dados);
        $prontuario->save();

        $this->auditoriaService->registrar('prontuarios', $prontuario->id, 'atualizado', $usuarioId, [
            'antes' => $original,
            'depois' => $prontuario->fresh()->toArray(),
        ]);

        return $prontuario;
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
