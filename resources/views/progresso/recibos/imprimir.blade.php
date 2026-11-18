<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recibo {{ $recibo->numero }}</title>
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
        .marca { display: inline-block; vertical-align: middle; }
        .nome-escola { font-size: 19px; color: #27685e; font-weight: 700; }
        .subtitulo { color: #718081; font-size: 10px; margin-top: 5px; }
        .titulo { font-size: 27px; color: #619c94; font-weight: 700; font-style: italic; }
        .numero-topo { font-size: 12px; font-weight: 700; margin-top: 6px; }
        .duas-colunas { width: 100%; border-collapse: collapse; margin-top: 18px; }
        .duas-colunas td { width: 50%; border: 1px solid #dce6e4; padding: 13px; vertical-align: top; line-height: 1.8; }
        .etiqueta { color: #568c85; text-transform: uppercase; font-weight: 700; letter-spacing: .5px; font-size: 10px; margin-bottom: 5px; }
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
        .facturas { padding: 9px 11px; background: #f2f8f6; border-left: 3px solid #619c94; }
        .nota { font-size: 10px; color: #697b78; line-height: 1.6; margin-top: 8px; }
        .totais { width: 55%; margin: 20px 0 0 auto; border-collapse: collapse; }
        .totais td { padding: 8px 10px; border-bottom: 1px solid #e1e8e6; }
        .totais td:last-child { text-align: right; font-weight: 700; }
        .totais .total-final td { background: #619c94; color: #fff; font-size: 14px; padding: 12px 10px; }
        .rodape { border-top: 1px solid #dce6e4; margin-top: 45px; padding-top: 15px; text-align: center; color: #718081; font-size: 10px; line-height: 1.8; }
        @media print {
            body { background: white; }
            .acoes { display: none; }
            .documento { max-width: none; min-height: auto; margin: 0; padding: 0; box-shadow: none; }
            .tabela th, .totais .total-final td, .facturas { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
@php
    $moeda = fn ($valor) => number_format((float) $valor, 2, ',', '.') . ' MT';
    $nomeEmissor = $emissor->nome ?? $emissor->name ?? ('Utilizador #' . $recibo->user_id);
    $logo = $logoUrl ?? null;
    // IVA/multa são informativos e apenas aparecem quando o controller fornecer valores fiáveis.
    $ivaInformativo = $ivaInformativo ?? null;
    $multaInformativa = $multaInformativa ?? null;
@endphp
<div class="acoes"><button class="botao" type="button" onclick="window.print()">Imprimir / Guardar PDF</button></div>
<div class="documento">
    <div class="cabecalho">
        <div class="cab-esquerda">
                <img class="logo" src="{{ URL('/logotipo/progresso.png') }}" heigth="100" width="100" alt="Colégio Progresso" />
            <div class="marca">

            </div>
        </div>
        <div class="cab-direita">
            <div class="titulo">Recibo</div>
            <div class="numero-topo">{{ $recibo->numero }}</div>
        </div>
    </div>

    <table class="duas-colunas">
        <tr>
            <td style="width:55%">
                <strong>{{ $escola->nome }}</strong>
                @if(!empty($escola->nuit))<div><strong>NUIT:</strong> {{ $escola->nuit }}</div>@endif
                @if(!empty($escola->provincia))<div><strong>Província:</strong> {{ $escola->provincia }}</div>@endif
                @if(!empty($escola->telefone))<div><strong>Telefone:</strong> {{ $escola->telefone }}</div>@endif
                @if(!empty($escola->endereco))<div><strong>Endereço:</strong> {{ $escola->endereco }}</div>@endif

            </td>
            <td>
                <div class="cliente">Exmo. Sr.(a) {{ $aluno->nome ?? ('Aluno #' . $aluno->id) }}</div>
            </td>
        </tr>
    </table>

    <table class="meta">
        <tr>
            <td><strong>N.º do documento</strong>{{ $recibo->numero }}</td>
            <td><strong>Data de emissão</strong>{{ \Carbon\Carbon::parse($recibo->data_emissao)->format('d/m/Y') }}</td>
            <td><strong>Moeda</strong>Meticais (MZN)</td>
            <td><strong>Estado</strong>{{ $recibo->estado }}</td>
        </tr>
    </table>

    <div class="seccao">Discriminação do pagamento</div>
    <table class="tabela">
        <thead><tr><th style="width:55px">Qtd.</th><th>Descrição</th><th class="direita">Valor liquidado</th></tr></thead>
        <tbody>
            @forelse($itens as $item)
                <tr><td class="centro">1</td><td>{{ $item->descricao }}</td><td class="direita">{{ $moeda($item->valor_aplicado) }}</td></tr>
            @empty
                <tr><td colspan="3" class="centro">Sem itens associados</td></tr>
            @endforelse
        </tbody>
    </table>

    @if($facturas->isNotEmpty())
        <div class="seccao">Facturas associadas</div>
        <div class="facturas">{{ $facturas->implode(', ') }}</div>
    @endif

    <div class="seccao">Meios de pagamento</div>
    <table class="tabela">
        <thead><tr><th>Meio</th><th>Conta financeira</th><th class="direita">Valor recebido</th></tr></thead>
        <tbody>
            @forelse($meios as $meio)
                <tr>
                    <td>{{ ['NUMERARIO'=>'Numerário','DEPOSITO'=>'Depósito','TRANSFERENCIA'=>'Transferência','MPESA'=>'M-Pesa','EMOLA'=>'e-Mola','MKESH'=>'mKesh','CARTAO'=>'Cartão'][$meio->meio] ?? $meio->meio }}</td>
                    <td>{{ $meio->conta_nome }}</td>
                    <td class="direita">{{ $moeda($meio->valor) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="centro">Pagamento efectuado com saldo do aluno</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="totais">
        @if($ivaInformativo !== null)
            <tr><td>IVA associado à(s) factura(s) *</td><td>{{ $moeda($ivaInformativo) }}</td></tr>
        @endif
        @if($multaInformativa !== null)
            <tr><td>Multa associada à(s) factura(s) *</td><td>{{ $moeda($multaInformativa) }}</td></tr>
        @endif
        @if((float)$recibo->valor_credito_utilizado > 0)
            <tr><td>Saldo anterior utilizado</td><td>{{ $moeda($recibo->valor_credito_utilizado) }}</td></tr>
        @endif
        <tr><td>Valor recebido</td><td>{{ $moeda($recibo->valor_recebido) }}</td></tr>
        @if((float)$recibo->valor_troco > 0)
            <tr><td>Troco devolvido</td><td>{{ $moeda($recibo->valor_troco) }}</td></tr>
        @endif
        @if((float)$recibo->valor_credito_gerado > 0)
            <tr><td>Crédito gerado</td><td>{{ $moeda($recibo->valor_credito_gerado) }}</td></tr>
        @endif
        <tr class="total-final"><td>Total liquidado</td><td>{{ $moeda($recibo->valor_aplicado) }}</td></tr>
    </table>
    @if($ivaInformativo !== null || $multaInformativa !== null)
        <p class="nota">* Valores informativos da(s) factura(s) associada(s), já incluídos nos valores facturados. Não são somados novamente ao pagamento nem representam necessariamente a parcela fiscal deste recibo.</p>
    @endif

    <div class="rodape">
        <div><strong>Emitido por:</strong> {{ $nomeEmissor }}</div>
        <div>Data de emissão: {{ \Carbon\Carbon::parse($recibo->data_emissao)->format('d-m-Y') }}</div>
        <div>{{ $escola->nome }} · Documento emitido pelo Sistema de Gestão Escolar</div>
    </div>
</div>
</body>
</html>
