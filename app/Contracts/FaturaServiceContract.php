<?php

namespace App\Contracts;

use App\Models\Agendamento;
use App\Models\Fatura;

interface FaturaServiceContract
{
    public function criarOuAtualizarPorAgendamento(Agendamento $agendamento): Fatura;

    public function registrarPagamento(int $faturaId, string $formaPagamento, ?string $transacaoId = null): Fatura;
}
