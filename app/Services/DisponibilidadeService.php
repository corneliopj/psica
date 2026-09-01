<?php

namespace App\Services;

use App\Contracts\DisponibilidadeServiceContract;
use App\Models\BloqueioAgenda;
use App\Models\Disponibilidade;
use Carbon\Carbon;
use InvalidArgumentException;

class DisponibilidadeService implements DisponibilidadeServiceContract
{
    public function gerarSlotsDoDia(int $profissionalId, string $data): array
    {
        $dia = Carbon::parse($data);
        $diaSemana = (int) $dia->dayOfWeek;

        $grade = Disponibilidade::query()
            ->where('profissional_id', $profissionalId)
            ->where('ativo', true)
            ->where('dia_semana', $diaSemana)
            ->orderBy('hora_inicio')
            ->get();

        $slots = [];

        foreach ($grade as $config) {
            $inicio = Carbon::parse($dia->toDateString() . ' ' . $config->hora_inicio);
            $fim = Carbon::parse($dia->toDateString() . ' ' . $config->hora_fim);
            $duracao = (int) $config->duracao_sessao;
            $intervalo = (int) $config->intervalo_entre_sessoes;

            $cursor = $inicio->copy();
            while ($cursor->copy()->addMinutes($duracao)->lessThanOrEqualTo($fim)) {
                $slotFim = $cursor->copy()->addMinutes($duracao);
                $slots[] = [
                    'inicio' => $cursor->toDateTimeString(),
                    'fim' => $slotFim->toDateTimeString(),
                ];
                $cursor->addMinutes($duracao + $intervalo);
            }
        }

        return $slots;
    }

    public function validarDisponibilidade(int $profissionalId, string $inicio, string $fim): bool
    {
        $inicioDt = Carbon::parse($inicio);
        $fimDt = Carbon::parse($fim);

        if ($fimDt->lessThanOrEqualTo($inicioDt)) {
            throw new InvalidArgumentException('A faixa de disponibilidade deve terminar após o início.');
        }

        $bloqueio = BloqueioAgenda::query()
            ->where('profissional_id', $profissionalId)
            ->where('data_inicio', '<', $fimDt)
            ->where('data_fim', '>', $inicioDt)
            ->exists();

        return ! $bloqueio;
    }

    public function criarBloqueioAgenda(int $profissionalId, string $inicio, string $fim, string $motivo, string $tipo = 'outro'): void
    {
        $inicioDt = Carbon::parse($inicio);
        $fimDt = Carbon::parse($fim);

        if ($fimDt->lessThanOrEqualTo($inicioDt)) {
            throw new InvalidArgumentException('O bloqueio deve ter uma faixa de tempo válida.');
        }

        BloqueioAgenda::create([
            'profissional_id' => $profissionalId,
            'data_inicio' => $inicioDt,
            'data_fim' => $fimDt,
            'motivo' => $motivo,
            'tipo' => $tipo,
        ]);
    }

    public function removerBloqueioAgenda(int $bloqueioId): bool
    {
        $bloqueio = BloqueioAgenda::find($bloqueioId);

        if (! $bloqueio) {
            return false;
        }

        return (bool) $bloqueio->delete();
    }
}
