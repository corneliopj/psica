<?php

namespace App\Services;

use App\Contracts\AuditoriaServiceContract;
use App\Models\Auditoria;

class AuditoriaService implements AuditoriaServiceContract
{
    public function registrar(string $entidade, int $entidadeId, string $acao, ?int $usuarioId, array $dados = []): void
    {
        Auditoria::create([
            'usuario_id' => $usuarioId,
            'entidade' => $entidade,
            'entidade_id' => $entidadeId,
            'acao' => $acao,
            'campos_alterados' => $dados['campos_alterados'] ?? null,
            'valor_antigo' => $dados['valor_antigo'] ?? null,
            'valor_novo' => $dados['valor_novo'] ?? null,
            'ip' => $dados['ip'] ?? request()->ip(),
            'user_agent' => $dados['user_agent'] ?? request()->userAgent(),
        ]);
    }

    public function listarPorEntidade(string $entidade, int $entidadeId): array
    {
        return Auditoria::query()
            ->where('entidade', $entidade)
            ->where('entidade_id', $entidadeId)
            ->orderByDesc('created_at')
            ->get()
            ->toArray();
    }
}
