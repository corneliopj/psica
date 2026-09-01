<?php

namespace App\Contracts;

interface AuditoriaServiceContract
{
    public function registrar(string $entidade, int $entidadeId, string $acao, ?int $usuarioId, array $dados = []): void;

    public function listarPorEntidade(string $entidade, int $entidadeId): array;
}
