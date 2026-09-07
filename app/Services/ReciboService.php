<?php

namespace App\Services;

use App\Contracts\ReciboServiceContract;
use App\Models\Fatura;
use App\Models\Recibo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class ReciboService implements ReciboServiceContract
{
    public function emitirReciboDaFatura(int $faturaId): Recibo
    {
        return DB::transaction(function () use ($faturaId) {
            $fatura = Fatura::query()
                ->with(['agendamento', 'paciente', 'profissional'])
                ->lockForUpdate()
                ->findOrFail($faturaId);

            if ($fatura->status !== 'pago') {
                throw new InvalidArgumentException('Recibo só pode ser emitido para faturas pagas.');
            }

            $existente = Recibo::query()->where('fatura_id', $fatura->id)->lockForUpdate()->first();
            if ($existente instanceof Recibo) {
                return $existente;
            }

            $snapshot = $this->montarSnapshotFiscal($fatura);
            $textoLegal = sprintf(
                'Recebi de %s, CPF %s, a quantia de R$ %s referente a serviços prestados de Atendimento Psicanalítico no dia %s.',
                $snapshot['pagador_nome'],
                $snapshot['pagador_documento'],
                number_format((float) $fatura->valor, 2, ',', '.'),
                Carbon::parse($snapshot['data_sessao'])->format('d/m/Y')
            );

            $hash = hash('sha256', json_encode([
                'numero' => $snapshot['numero'],
                'fatura_id' => $fatura->id,
                'valor' => (float) $fatura->valor,
                'pagador_documento' => $snapshot['pagador_documento'],
                'psicanalista_documento' => $snapshot['psicanalista_documento'],
                'data_sessao' => $snapshot['data_sessao'],
                'texto_legal' => $textoLegal,
            ], JSON_UNESCAPED_UNICODE));

            $recibo = Recibo::create([
                'fatura_id' => $fatura->id,
                'numero' => $snapshot['numero'],
                'snapshot_fiscal' => $snapshot,
                'texto_legal' => $textoLegal,
                'hash_autenticidade' => $hash,
                'emitido_em' => now(),
            ]);

            $arquivo = $this->gerarPdf($recibo);
            $recibo->arquivo_pdf = $arquivo;
            $recibo->save();

            $fatura->numero_recibo = $snapshot['numero'];
            $fatura->emitida_em = now();
            $fatura->save();

            return $recibo;
        });
    }

    public function gerarPdf(Recibo $recibo): string
    {
        $caminho = 'recibos/' . $recibo->numero . '.pdf';

        if (class_exists('Barryvdh\\DomPDF\\Facade\\Pdf')) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('recibos.pdf', ['recibo' => $recibo]);
            Storage::disk('local')->put($caminho, $pdf->output());
            return $caminho;
        }

        // Fallback para ambiente sem DomPDF instalado no runtime.
        $html = view('recibos.pdf', ['recibo' => $recibo])->render();
        Storage::disk('local')->put($caminho, $html);

        return $caminho;
    }

    protected function montarSnapshotFiscal(Fatura $fatura): array
    {
        $paciente = $fatura->paciente;
        $profissional = $fatura->profissional;
        $agendamento = $fatura->agendamento;

        $usaResponsavel = Schema::hasColumn('pacientes', 'cpf_responsavel')
            && ! empty($paciente?->cpf_responsavel);

        $pagadorNome = $usaResponsavel
            ? ($paciente?->nome_responsavel ?: $paciente?->nome ?: $paciente?->name ?: 'Pagador não informado')
            : ($paciente?->nome ?: $paciente?->name ?: 'Pagador não informado');

        $pagadorDocumento = $usaResponsavel
            ? (string) $paciente?->cpf_responsavel
            : ((string) ($paciente?->cpf ?? 'Não informado'));

        $numero = $this->gerarNumeroSequencial();

        return [
            'numero' => $numero,
            'psicanalista_nome' => (string) ($profissional?->nome ?? 'Não informado'),
            'psicanalista_documento' => (string) ($profissional?->cpf_cnpj ?? 'Não informado'),
            'pagador_nome' => $pagadorNome,
            'pagador_documento' => $pagadorDocumento,
            'valor' => (float) $fatura->valor,
            'data_sessao' => (string) Carbon::parse($agendamento?->scheduled_at ?? now())->toDateString(),
        ];
    }

    protected function gerarNumeroSequencial(): string
    {
        $ano = now()->year;
        $prefixo = sprintf('REC-%d-', $ano);

        $ultimo = Recibo::query()
            ->where('numero', 'like', $prefixo . '%')
            ->lockForUpdate()
            ->orderByDesc('numero')
            ->first();

        $sequencial = 1;

        if ($ultimo instanceof Recibo) {
            $partes = explode('-', $ultimo->numero);
            $ultimoNumero = (int) end($partes);
            $sequencial = $ultimoNumero + 1;
        }

        return sprintf('%s%04d', $prefixo, $sequencial);
    }
}
