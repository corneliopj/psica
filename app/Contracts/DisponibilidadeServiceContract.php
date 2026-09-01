<?php

namespace App\Contracts;

interface DisponibilidadeServiceContract
{
    public function gerarSlotsDoDia(int $profissionalId, string $data): array;

    public function validarDisponibilidade(int $profissionalId, string $inicio, string $fim): bool;

    public function criarBloqueioAgenda(int $profissionalId, string $inicio, string $fim, string $motivo, string $tipo = 'outro'): void;

    public function removerBloqueioAgenda(int $bloqueioId): bool;
}
