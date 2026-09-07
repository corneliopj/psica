<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Recibo {{ $recibo->numero }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        .container { max-width: 760px; margin: 0 auto; }
        .header { margin-bottom: 20px; }
        .label { font-weight: bold; }
        .box { border: 1px solid #ddd; padding: 12px; margin-top: 10px; }
        .footer { margin-top: 24px; font-size: 10px; color: #555; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2>Recibo de Psicanálise</h2>
        <p><span class="label">Número:</span> {{ $recibo->numero }}</p>
        <p><span class="label">Emitido em:</span> {{ optional($recibo->emitido_em)->format('d/m/Y H:i') }}</p>
    </div>

    <div class="box">
        <p>{{ $recibo->texto_legal }}</p>
    </div>

    <div class="box">
        <p><span class="label">Psicanalista:</span> {{ $recibo->snapshot_fiscal['psicanalista_nome'] ?? 'Não informado' }}</p>
        <p><span class="label">CPF/CNPJ:</span> {{ $recibo->snapshot_fiscal['psicanalista_documento'] ?? 'Não informado' }}</p>
        <p><span class="label">Pagador:</span> {{ $recibo->snapshot_fiscal['pagador_nome'] ?? 'Não informado' }}</p>
        <p><span class="label">CPF do Pagador:</span> {{ $recibo->snapshot_fiscal['pagador_documento'] ?? 'Não informado' }}</p>
        <p><span class="label">Valor:</span> R$ {{ number_format((float) ($recibo->snapshot_fiscal['valor'] ?? 0), 2, ',', '.') }}</p>
        <p><span class="label">Data da Sessão:</span> {{ \Carbon\Carbon::parse($recibo->snapshot_fiscal['data_sessao'] ?? now())->format('d/m/Y') }}</p>
    </div>

    <div class="footer">
        <p>Hash de autenticidade (SHA-256): {{ $recibo->hash_autenticidade }}</p>
    </div>
</div>
</body>
</html>
