<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Factura {{ $factura->numero }}</title>
    <style>
        @page { size: A4 portrait; margin: 13mm; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #f3f5f5; font-family: Arial, Helvetica, sans-serif; color: #253536; font-size: 11px; }
        .acoes { text-align: center; padding: 18px; }
        .botao { display: inline-block; border: 0; border-radius: 6px; background: #377f76; color: #fff; padding: 11px 22px; font-size: 13px; cursor: pointer; }
        .documento { max-width: 190mm; min-height: 255mm; margin: 0 auto 25px; padding: 20px 24px; background: #fff; box-shadow: 0 3px 18px #00000012; }
        .cabecalho { display: table; width: 100%; border-bottom: 3px solid #619c94; padding-bottom: 15px; }
        .cab-esquerda, .cab-direita { display: table-cell; vertical-align: middle; }
        .cab-direita { text-align: right; }
        .logo { max-width: 75px; max-height: 75px; object-fit: contain; vertical-align: middle; margin-right: 12px; }
        .titulo { font-size: 27px; color: #619c94; font-weight: 700; font-style: italic; }
        .numero-topo { font-size: 12px; font-weight: 700; margin-top: 6px; }
        .duas-colunas { width: 100%; border-collapse: collapse; margin-top: 18px; }
        .duas-colunas td { width: 50%; border: 1px solid #dce6e4; padding: 13px; vertical-align: top; line-height: 1.8; }
        .cliente { font-weight: 700; font-size: 12px; }
        .meta { width: 100%; border-collapse: collapse; margin-top: 16px; }
        .meta td { border: 1px solid #e0e6e5; padding: 9px; }
        .meta strong { display: block; margin-bottom: 5px; color: #54716d; }
        .seccao { margin-top: 23px; margin-bottom: 9px; font-size: 12px; font-weight: 700; color: #27685e; }
        .tabela { width: 100%; border-collapse: collapse; }
        .tabela th { background: #619c94; color: #fff; padding: 9px; text-align: left; }
        .tabela td { padding: 9px; border: 1px solid #e1e8e6; }
        .direita { text-align: right !important; }
        .centro { text-align: center !important; }
        .nota { font-size: 10px; color: #697b78; line-height: 1.6; margin-top: 8px; }
        .totais { width: 55%; margin: 20px 0 0 auto; border-collapse: collapse; }
        .totais td { padding: 8px 10px; border-bottom: 1px solid #e1e8e6; }
        .totais td:last-child { text-align: right; font-weight: 700; }
        .totais .total-final td { background: #619c94; color: #fff; font-size: 14px; padding: 12px 10px; }
        .rodape { border-top: 1px solid #dce6e4; margin-top: 45px; padding-top: 15px; text-align: center; color: #718081; font-size: 10px; line-height: 1.8; }
        tr { page-break-inside: avoid; }
        @media print {
            body { background: white; }
            .acoes { display: none; }
            .documento { max-width: none; min-height: auto; margin: 0; padding: 0; box-shadow: none; }
            .tabela th, .totais .total-final td { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
@php
    $moeda = fn ($valor) => number_format((float) $valor, 2, ',', '.') . ' MT';
    $dataEmissao = $factura->data_emissao ?? $factura->created_at ?? now();
    $nomeEmissor = $emissor->nome ?? $emissor->name ?? 'Não identificado';
    $valorDesconto = (float) ($factura->valor_desconto ?? $factura->desconto ?? 0);
    $valorIva = (float) ($factura->valor_iva ?? $factura->iva ?? 0);
    $valorMulta = (float) ($factura->valor_multa ?? $factura->multa ?? 0);
    $totalPago = (float) ($totalPago ?? 0);
    $totalFactura = (float) ($totalFactura ?? 0);
    $saldoPendente = (float) ($saldoPendente ?? 0);
    $estadoFinanceiro = $saldoPendente <= 0.009 ? 'Paga' : ($totalPago > 0 ? 'Parcialmente paga' : 'Pendente');
@endphp

<div class="acoes">
    <button class="botao" type="button" onclick="window.print()">Imprimir / Guardar PDF</button>
</div>

<div class="documento">
    <div class="cabecalho">
        <div class="cab-esquerda">
            <img class="logo" src="{{ URL('/logotipo/progresso.png') }}" heigth="100" width="100" alt="Logótipo do Colégio Progresso">
        </div>
        <div class="cab-direita">
            <div class="titulo">Factura</div>
            <div class="numero-topo">{{ $factura->numero }}</div>
        </div>
    </div>

    <table class="duas-colunas">
        <tr>
            <td style="width:55%">
                <strong>{{ $escola->nome }}</strong>
                @if(!empty($escola->endereco))<div><strong></strong> {{ $escola->endereco }}</div>@endif
                @if(!empty($escola->provincia))<div><strong></strong> {{ $escola->provincia }}</div>@endif
                @if(!empty($escola->nuit))<div><strong>NUIT:</strong> {{ $escola->nuit }}</div>@endif
                @if(!empty($escola->telefone))<div><strong>Telefone:</strong> {{ $escola->telefone }}</div>@endif

            </td>
            <td>
                <div class="cliente">Exmo. Sr.(a) {{ $aluno->nome ?? ('Aluno #' . $aluno->id) }}</div>
            </td>
        </tr>
    </table>

    <table class="meta">
        <tr>
            <td><strong>N.º do documento</strong>{{ $factura->numero }}</td>
            <td><strong>Data de emissão</strong>{{ \Carbon\Carbon::parse($dataEmissao)->format('d-m-Y') }}</td>
            <td><strong>Moeda</strong>Meticais (MZN)</td>
            <td><strong>Estado financeiro</strong>{{ $estadoFinanceiro }}</td>
        </tr>
    </table>

    <div class="seccao">Discriminação da factura</div>
    <table class="tabela">
        <thead>
            <tr>
                <th style="width:55px">Qtd.</th>
                <th>Descrição</th>
                <th class="direita">Valor facturado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($itens as $item)
                @php
                    $cobranca = $cobrancas[$item->cobranca_id ?? 0] ?? null;
                    $descricao = $item->descricao ?? $cobranca->descricao ?? 'Cobrança escolar';
                    $valorItem = $item->valor_total ?? $item->valor ?? $cobranca->valor_total ?? 0;
                @endphp
                <tr>
                    <td class="centro">1</td>
                    <td>{{ $descricao }}</td>
                    <td class="direita">{{ $moeda($valorItem) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="centro">Sem itens associados</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="totais">
        @if($valorDesconto > 0)
            <tr><td>Desconto</td><td>- {{ $moeda($valorDesconto) }}</td></tr>
        @endif
        <tr><td>IVA registado</td><td>{{ $moeda($valorIva) }}</td></tr>
        <tr><td>Multa registada</td><td>{{ $moeda($valorMulta) }}</td></tr>
        <tr class="total-final"><td>Total da factura</td><td>{{ $moeda($totalFactura) }}</td></tr>
        @if($moeda($totalPago) > 0)
          <tr><td>Total pago</td><td>{{ $moeda($totalPago) }}</td></tr>
          <tr><td>Saldo pendente</td><td>{{ $moeda($saldoPendente) }}</td></tr>
        @endif
    </table>

    <div class="rodape">
        <div><strong>Emitido por:</strong> {{ $nomeEmissor }}</div>
        <div>Data de emissão: {{ \Carbon\Carbon::parse($dataEmissao)->format('d-m-Y') }}</div>
        <div>{{ $escola->nome }} · Documento emitido pelo Sistema de Gestão Escolar</div>
    </div>
</div>
</body>
</html>