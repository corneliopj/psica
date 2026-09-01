<?php

namespace App\Contracts;

interface NotificacaoServiceContract
{
    public function enviarConfirmacao(int $agendamentoId): bool;

    public function enviarCancelamento(int $agendamentoId, ?string $motivo = null): bool;

    public function enviarLembrete(int $agendamentoId, string $tipo): bool;

    public function enviarReagendamento(int $agendamentoId): bool;
}
