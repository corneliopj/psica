<?php

namespace App\Http\Controllers;

use App\Contracts\FaturaServiceContract;
use App\Contracts\ReciboServiceContract;
use App\Models\Recibo;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReciboController extends Controller
{
    public function __construct(
        protected FaturaServiceContract $faturaService,
        protected ReciboServiceContract $reciboService,
    ) {}

    protected function garantirPerfilPermitido(Request $request, array $perfisPermitidos): void
    {
        abort_unless(in_array($request->user()?->perfil, $perfisPermitidos, true), 403);
    }

    public function registrarPagamento(Request $request, int $faturaId): JsonResponse
    {
        $this->garantirPerfilPermitido($request, ['admin', 'profissional']);

        $data = $request->validate([
            'forma_pagamento' => ['required', 'string', 'max:50'],
            'transacao_id' => ['nullable', 'string', 'max:120'],
        ]);

        $fatura = $this->faturaService->registrarPagamento(
            $faturaId,
            $data['forma_pagamento'],
            $data['transacao_id'] ?? null,
        );

        return response()->json([
            'fatura' => [
                'id' => $fatura->id,
                'status' => $fatura->status,
                'pago_em' => $fatura->pago_em,
            ],
        ]);
    }

    public function emitir(Request $request, int $faturaId): JsonResponse
    {
        $this->garantirPerfilPermitido($request, ['admin', 'profissional']);

        $recibo = $this->reciboService->emitirReciboDaFatura($faturaId);

        return response()->json([
            'recibo' => [
                'id' => $recibo->id,
                'numero' => $recibo->numero,
                'hash_autenticidade' => $recibo->hash_autenticidade,
                'download_url' => route('recibos.pdf', $recibo),
            ],
        ]);
    }

    public function pdf(Request $request, Recibo $recibo): BinaryFileResponse
    {
        $this->garantirPerfilPermitido($request, ['admin', 'profissional', 'paciente']);

        if (empty($recibo->arquivo_pdf) || ! Storage::disk('local')->exists($recibo->arquivo_pdf)) {
            $arquivo = $this->reciboService->gerarPdf($recibo);
            $recibo->arquivo_pdf = $arquivo;
            $recibo->save();
        }

        return response()->download(Storage::path($recibo->arquivo_pdf), $recibo->numero . '.pdf');
    }
}
