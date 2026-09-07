<?php

namespace App\Services;

use App\Contracts\FaturaServiceContract;
use App\Models\Agendamento;
use App\Models\Fatura;
use Illuminate\Support\Facades\DB;

class FaturaService implements FaturaServiceContract
{
    public function criarOuAtualizarPorAgendamento(Agendamento $agendamento): Fatura
    {
        return DB::transaction(function () use ($agendamento) {
            $fatura = Fatura::query()
                ->where('agendamento_id', $agendamento->id)
                ->lockForUpdate()
                ->first();

            if (! $fatura instanceof Fatura) {
                return Fatura::create([
                    'agendamento_id' => $agendamento->id,
                    'paciente_id' => $agendamento->paciente_id,
                    'profissional_id' => $agendamento->profissional_id,
                    'valor' => (float) ($agendamento->valor_sessao ?? 0),
                    'status' => 'pendente',
                ]);
            }

            if ($fatura->status !== 'pago') {
                $fatura->setAttribute('valor', (float) ($agendamento->valor_sessao ?? $fatura->valor));
                $fatura->save();
            }

            return $fatura;
        });
    }

    public function registrarPagamento(int $faturaId, string $formaPagamento, ?string $transacaoId = null): Fatura
    {
        return DB::transaction(function () use ($faturaId, $formaPagamento, $transacaoId) {
            $fatura = Fatura::query()->lockForUpdate()->findOrFail($faturaId);

            $fatura->status = 'pago';
            $fatura->forma_pagamento = $formaPagamento;
            $fatura->transacao_id = $transacaoId;
            $fatura->pago_em = now();
            $fatura->save();

            return $fatura;
        });
    }
}
