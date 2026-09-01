<?php

namespace App\Services;

use App\Contracts\NotificacaoServiceContract;
use App\Models\Agendamento;
use App\Models\Notificacao;
use App\Models\Usuario;

class NotificacaoService implements NotificacaoServiceContract
{
    public function enviarConfirmacao(int $agendamentoId): bool
    {
        return $this->enviarPorAgendamento($agendamentoId, 'confirmacao', 'email', 'Agendamento confirmado');
    }

    public function enviarCancelamento(int $agendamentoId, ?string $motivo = null): bool
    {
        return $this->enviarPorAgendamento($agendamentoId, 'cancelamento', 'email', 'Agendamento cancelado', $motivo);
    }

    public function enviarLembrete(int $agendamentoId, string $tipo): bool
    {
        $canal = $tipo === '24h' ? 'lembrente' : 'lembrente';
        return $this->enviarPorAgendamento($agendamentoId, $canal, 'email', 'Lembrete de sessão');
    }

    public function enviarReagendamento(int $agendamentoId): bool
    {
        return $this->enviarPorAgendamento($agendamentoId, 'reagendamento', 'email', 'Reagendamento realizado');
    }

    protected function enviarPorAgendamento(int $agendamentoId, string $canal, string $tipo, string $assunto, ?string $motivo = null): bool
    {
        $agendamento = Agendamento::find($agendamentoId);

        if (! $agendamento) {
            return false;
        }

        $usuario = Usuario::find($agendamento->paciente?->usuario_id ?? 0);

        if (! $usuario) {
            return false;
        }

        Notificacao::create([
            'usuario_id' => $usuario->id,
            'tipo' => $tipo,
            'canal' => $canal,
            'assunto' => $assunto,
            'mensagem' => $motivo ?? 'Notificação de sessão.',
            'status' => 'pendente',
        ]);

        return true;
    }
}
