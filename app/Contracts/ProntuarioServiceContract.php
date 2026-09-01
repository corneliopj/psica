<?php

namespace App\Contracts;

use App\Models\Prontuario;

interface ProntuarioServiceContract
{
    public function criarProntuario(int $agendamentoId, int $pacienteId, int $profissionalId, ?string $anotacoes = null, ?string $historicoClinico = null): Prontuario;

    public function atualizarProntuario(int $prontuarioId, int $usuarioId, array $dados): Prontuario;

    public function visualizarProntuario(int $prontuarioId, int $usuarioId): Prontuario;
}
