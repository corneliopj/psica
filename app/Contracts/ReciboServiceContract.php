<?php

namespace App\Contracts;

use App\Models\Recibo;

interface ReciboServiceContract
{
    public function emitirReciboDaFatura(int $faturaId): Recibo;

    public function gerarPdf(Recibo $recibo): string;
}
