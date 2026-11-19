@extends('layouts.app')

@push('css')
<style>
    .pg-card {
        border: 1px solid #e9ecef;
        border-radius: 10px;
        padding: 18px;
        margin-bottom: 18px;
        background: #fff;
    }

    .pg-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 15px;
    }

    .pg-group {
        margin-bottom: 15px;
        padding-bottom: 12px;
        border-bottom: 1px solid #eee;
    }

    .pg-group:last-child {
        border-bottom: none;
    }

    .pg-group-title {
        font-weight: 600;
        margin-bottom: 10px;
    }

    .pg-charge {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 9px 0;
    }

    .pg-charge-info {
        flex: 1;
        min-width: 0;
    }

    .pg-charge-value {
        white-space: nowrap;
        font-weight: 600;
    }

    .pg-summary-line {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 8px 0;
    }

    .pg-summary-total {
        border-top: 1px solid #dee2e6;
        margin-top: 8px;
        padding-top: 14px;
        font-size: 19px;
        font-weight: 700;
    }

    .pg-excess {
        border: 1px solid #ffe69c;
        background: #fff9e6;
        border-radius: 10px;
        padding: 16px;
        margin-top: 15px;
    }

    .pg-payment-row {
        border: 1px solid #e9ecef;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 12px;
    }

    .pg-sticky {
        position: sticky;
        top: 85px;
    }

    @media (max-width: 1199px) {
        .pg-sticky {
            position: static;
        }
    }
</style>
@endpush

@section('conteudo')

@php
    $grupos = [
        'MATRICULA' => 'Matrículas',
        'RENOVACAO' => 'Renovações',
        'PROPINA' => 'Propinas',
        'SERVICO' => 'Serviços',
        'AULA_EXTRA' => 'Actividades extras',
        'CURSO_FERIAS' => 'Férias',
        'MULTA' => 'Multas',
    ];

    $cobrancasAgrupadas = $cobrancas->groupBy('tipo');
    $saldoDisponivel = max(0, (float) $saldoAluno);
@endphp

<div class="container-fluid">
    <div class="row">
        <div class="col-xl-12">

            <div class="card height-equal title-line">

                <div class="card-header">
                    <h2>Pagamento da Factura {{ $facturaSelecionada->numero }}</h2>
                    <p class="f-m-light mt-1">
                        Liquidação dos itens da factura emitida
                    </p>
                </div>

                <div class="card-body basic-wizard important-validation">

                <form id="formPagamento"
                        action="{{ route('progresso.pagamentos.store') }}"
                        method="POST"
                        class="row g-3 custom-input">

                        @csrf

                        <input type="hidden"
                               name="aluno_id"
                               value="{{ $aluno->id }}">

                        <input type="hidden" name="factura_id" value="{{ $facturaSelecionada->id }}">

                        <input type="hidden" name="chave_idempotencia" id="chave_idempotencia" value="{{ (string) \Illuminate\Support\Str::uuid() }}">

                        {{-- IDENTIFICAÇÃO --}}
                        <div class="col-12">
                            <div class="pg-card">
                                <div class="pg-title">
                                    1. Identificação do aluno
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <small class="text-muted">Aluno</small>
                                        <div class="fw-semibold">
                                            {{ $aluno->nome ?? 'Aluno #'.$aluno->id }}
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <small class="text-muted">Código</small>
                                        <div class="fw-semibold">
                                            {{ $aluno->codigo ?? '—' }}
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <small class="text-muted">Classe</small>
                                        <div class="fw-semibold">
                                            {{ $aluno->classe_nome ?? '—' }}
                                        </div>
                                    </div>

                                    <div class="col-md-6" hidden>
                                        <small class="text-muted">Escola</small>
                                        <div class="fw-semibold">
                                            {{ $escola->nome ?? '—' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- COBRANÇAS --}}
                        <div class="col-xl-12">
                            <div class="pg-card">
                                <div class="pg-title d-flex justify-content-between">
                                    <span>2. Itens da factura</span>
                                    <small class="text-muted">
                                        <span id="quantidadeSelecionada">0</span>
                                        seleccionadas
                                    </small>
                                </div>

                                @forelse($grupos as $tipo => $titulo)
                                    @if($cobrancasAgrupadas->has($tipo))
                                        <div class="pg-group">
                                            <div class="pg-group-title">
                                                {{ $titulo }}
                                            </div>

                                            @foreach($cobrancasAgrupadas[$tipo] as $cobranca)
                                                @php
                                                    $principal = max(
                                                        0,
                                                        (float) ($cobranca->saldo_principal ?? $cobranca->valor_total)
                                                    );

                                                    $multa = max(
                                                        0,
                                                        (float) ($cobranca->multa_simulada ?? 0)
                                                    );
                                                @endphp

                                                <div class="pg-charge">
                                                    <div class="pg-charge-info">
                                                        <div class="form-check">
                                                            <input
                                                                type="checkbox"
                                                                class="form-check-input pg-cobranca"
                                                                name="cobrancas[]"
                                                                value="{{ $cobranca->id }}"
                                                                id="cobranca_{{ $cobranca->id }}"
                                                                data-principal="{{ $principal }}"
                                                                data-multa="0"
                                                                data-desconto="0"
                                                                data-iva="0"
                                                                data-taxa-iva="0"
                                                                checked
                                                                onclick="return false;"
                                                            >

                                                            <label class="form-check-label"
                                                                   for="cobranca_{{ $cobranca->id }}">
                                                                <strong>
                                                                    {{ $cobranca->descricao }}
                                                                </strong>

                                                                <div class="small text-muted" hidden>
                                                                    @if($cobranca->data_vencimento)
                                                                        Vencimento:
                                                                        {{ \Carbon\Carbon::parse($cobranca->data_vencimento)->format('d/m/Y') }}
                                                                    @endif

                                                                    @if($cobranca->estado === 'PARCIAL')
                                                                        · Parcialmente paga
                                                                    @endif
                                                                </div>
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="pg-charge-value">
                                                        {{ number_format($principal, 2, ',', '.') }} MT
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                @empty
                                    <p class="text-muted">
                                        Não existem cobranças pendentes.
                                    </p>
                                @endforelse
                            </div>
                        </div>

                        {{-- MEIOS DE PAGAMENTO --}}
                        <div class="col-12">
                            <div class="pg-card">
                                <div class="pg-title d-flex justify-content-between align-items-center">
                                    <span>3. Meios de pagamento</span>

                                    <button type="button"
                                            class="btn btn-outline-primary btn-sm"
                                            id="adicionarMeio">
                                        + Adicionar meio
                                    </button>
                                </div>

                                <div id="listaMeios"></div>
                            </div>
                        </div>

                        {{-- RESUMO --}}
                        <div class="col-xl-12">
                            <div class="pg-sticky">
                                <div class="pg-card">

                                    <div class="pg-title">
                                        4. Resumo financeiro
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">
                                            Desconto
                                        </label>

                                        <div class="d-flex gap-3 flex-wrap">
                                            <div class="form-check">
                                                <input class="form-check-input"
                                                       type="radio"
                                                       name="tipo_desconto"
                                                       id="desconto_automatico"
                                                       value="AUTOMATICO"
                                                       checked>

                                                <label class="form-check-label"
                                                       for="desconto_automatico">
                                                    Automático
                                                </label>
                                            </div>

                                            <div class="form-check">
                                                <input class="form-check-input"
                                                       type="radio"
                                                       name="tipo_desconto"
                                                       id="desconto_manual"
                                                       value="MANUAL" disabled>

                                                <label class="form-check-label"
                                                       for="desconto_manual">
                                                    Manual (%)
                                                </label>
                                            </div>
                                        </div>

                                        <div id="areaDescontoManual"
                                             class="mt-2"
                                             style="display:none;">

                                            <label class="form-label">
                                                Percentagem de desconto
                                            </label>

                                            <input type="number"
                                                   class="form-control"
                                                   id="percentagem_desconto"
                                                   name="percentagem_desconto"
                                                   min="0"
                                                   max="100"
                                                   step="0.01"
                                                   value="0">
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="pg-summary-line">
                                        <span>Subtotal</span>
                                        <strong id="resumoSubtotal">0,00 MT</strong>
                                    </div>

                                    <div class="pg-summary-line">
                                        <span>Desconto</span>
                                        <strong class="text-success"
                                                id="resumoDesconto">
                                            - 0,00 MT
                                        </strong>
                                    </div>

                                    <div class="pg-summary-line">
                                        <span>IVA</span>
                                        <strong id="resumoIva">0,00 MT</strong>
                                    </div>

                                    <div class="pg-summary-line">
                                        <span>Multa</span>
                                        <strong id="resumoMulta">0,00 MT</strong>
                                    </div>

                                    @if($saldoDisponivel > 0)
                                        <div class="pg-summary-line"
                                             id="linhaSaldoUtilizado"
                                             style="display:none;">
                                            <span>Saldo utilizado automaticamente</span>
                                            <strong class="text-success"
                                                    id="resumoSaldoUtilizado">
                                                - 0,00 MT
                                            </strong>
                                        </div>
                                    @endif

                                    <div class="pg-summary-line pg-summary-total">
                                        <span>Valor a receber</span>
                                        <strong id="resumoValorReceber">
                                            0,00 MT
                                        </strong>
                                    </div>

                                    <div class="pg-summary-line">
                                        <span>Valor recebido</span>
                                        <strong id="resumoValorRecebido">
                                            0,00 MT
                                        </strong>
                                    </div>

                                    <div class="pg-summary-line">
                                        <span>Ainda por receber</span>
                                        <strong id="resumoDiferenca">
                                            0,00 MT
                                        </strong>
                                    </div>

                                    {{-- SÓ APARECE SE PAGAR A MAIS --}}
                                    <div class="pg-excess"
                                         id="areaExcedente"
                                         style="display:none;">

                                        <div class="fw-semibold mb-2">
                                            Valor pago a mais:
                                            <span id="valorExcedente">
                                                0,00 MT
                                            </span>
                                        </div>

                                        <p class="small mb-2">
                                            O que pretende fazer com este valor?
                                        </p>

                                        <div class="d-flex gap-3 flex-wrap mb-3">
                                            <div class="form-check">
                                                <input type="radio"
                                                       class="form-check-input"
                                                       name="destino_excedente"
                                                       id="destino_troco"
                                                       value="TROCO"
                                                       checked>

                                                <label class="form-check-label"
                                                       for="destino_troco">
                                                    Devolver como troco
                                                </label>
                                            </div>

                                            <div class="form-check">
                                                <input type="radio"
                                                       class="form-check-input"
                                                       name="destino_excedente"
                                                       id="destino_saldo"
                                                       value="SALDO">

                                                <label class="form-check-label"
                                                       for="destino_saldo">
                                                    Guardar no saldo do aluno
                                                </label>
                                            </div>
                                        </div>

                                        <div class="pg-summary-line">
                                            <span id="labelDestinoExcedente">
                                                Troco a devolver
                                            </span>

                                            <strong id="resumoDestinoExcedente">
                                                0,00 MT
                                            </strong>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        {{-- ACÇÕES DO PAGAMENTO --}}
                        <div class="col-12">
                            <div class="pg-card">
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="button"
                                            class="btn btn-outline-secondary"
                                            onclick="history.back()">
                                        Voltar
                                    </button>

                                    <button type="submit"
                                            class="btn btn-success"
                                            id="confirmarPagamento">
                                        <i class="fa fa-check"></i>
                                        Confirmar pagamento
                                    </button>

                                </div>

                            </div>
                        </div>

                    </form>
            </div>
        </div>
    </div>
</div>



@endsection

@push('js')
<script>
$(document).ready(function () {

    const contas = @json($contas);
    const saldoDisponivel = Number(@json($saldoDisponivel));
    const taxaIvaEscola = Number(@json((float) ($escola->iva ?? 0)));

    // Garante índices únicos e sequenciais, inclusive após remover linhas.
    function renumerarMeios() {
        $('#listaMeios .pg-payment-row').each(function (indice) {
            const linha = $(this);
            linha.attr('data-indice', indice);
            linha.find('.pg-conta').attr('name', `meios[${indice}][conta_financeira_id]`);
            linha.find('.pg-meio').attr('name', `meios[${indice}][meio]`);
            linha.find('.pg-valor-meio').attr('name', `meios[${indice}][valor]`);
        });
    }

    const dinheiro = valor => {
        return Number(valor || 0).toLocaleString('pt-PT', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }) + ' MT';
    };

    function adicionarMeio() {

        const indice = $('#listaMeios .pg-payment-row').length;

        let options = '<option value="">Seleccionar conta financeira</option>';

        contas.forEach(function (conta) {
            options += `
                <option value="${conta.id}">
                    ${$('<div>').text(conta.nome).html()}
                </option>
            `;
        });

        const html = `
            <div class="pg-payment-row" data-indice="${indice}">
                <div class="row g-3 align-items-end">

                    <div class="col-md-5">
                        <label class="form-label">Conta Financeira</label>

                        <select class="form-select pg-conta"
                                name="meios[${indice}][conta_financeira_id]">
                            ${options}
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Meio de pagamento</label>

                        <select class="form-select pg-meio"
                                name="meios[${indice}][meio]">
                            <option value="">Seleccionar meio</option>
                            <option value="NUMERARIO">Numerário</option>
                            <option value="DEPOSITO">Depósito</option>
                            <option value="TRANSFERENCIA">Transferência</option>
                            <option value="MPESA">M-Pesa</option>
                            <option value="EMOLA">e-Mola</option>
                            <option value="MKESH">mKesh</option>
                            <option value="CARTAO">Cartão</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Valor recebido</label>

                        <input type="number"
                               class="form-control pg-valor-meio"
                               name="meios[${indice}][valor]"
                               value="0"
                               min="0"
                               step="0.01">
                    </div>

                    <div class="col-md-1">
                        <button type="button"
                                class="btn btn-outline-danger pg-remover-meio"
                                title="Remover">
                            ×
                        </button>
                    </div>


                </div>
            </div>
        `;

        $('#listaMeios').append(html);
        renumerarMeios();
        actualizarResumo();
    }

    function actualizarResumo() {

        let subtotal = 0;
        let descontoAutomatico = 0;
        let ivaRegistado = 0;
        let multa = 0;

        $('.pg-cobranca:checked').each(function () {

            const principal = Number($(this).data('principal')) || 0;
            const desconto = Number($(this).data('desconto')) || 0;
            const iva = Number($(this).data('iva')) || 0;
            const valorMulta = Number($(this).data('multa')) || 0;

            /*
             * O saldo_principal já representa o valor pendente.
             * Para evitar descontar duas vezes valores de facturas
             * emitidas, o subtotal é apresentado como saldo pendente.
             */
            subtotal += principal;
            descontoAutomatico += desconto;
            ivaRegistado += iva;
            multa += valorMulta;
        });

        const tipoDesconto = $('input[name="tipo_desconto"]:checked').val();

        let desconto = 0;

        if (tipoDesconto === 'MANUAL') {

            const percentagem = Math.min(
                100,
                Math.max(
                    0,
                    Number($('#percentagem_desconto').val()) || 0
                )
            );

            desconto = subtotal * percentagem / 100;

        } else {

            /*
             * Descontos já incorporados nas cobranças emitidas
             * não são subtraídos novamente.
             */
            desconto = 0;
        }

        /*
         * IVA já incluído no saldo da cobrança não é somado novamente.
         * A linha IVA será informativa até reconciliarmos as facturas.
         */
        const iva = 0;

        const total = Math.max(
            0,
            subtotal - desconto + multa
        );

        const saldoUtilizado = Math.min(
            saldoDisponivel,
            total
        );

        const valorReceber = Math.max(
            0,
            total - saldoUtilizado
        );

        let valorRecebido = 0;

        $('.pg-valor-meio').each(function () {
            valorRecebido += Math.max(
                0,
                Number($(this).val()) || 0
            );
        });

        const diferenca = Math.max(
            0,
            valorReceber - valorRecebido
        );

        const excedente = Math.max(
            0,
            valorRecebido - valorReceber
        );

        $('#quantidadeSelecionada').text(
            $('.pg-cobranca:checked').length
        );

        $('#resumoSubtotal').text(dinheiro(subtotal));
        $('#resumoDesconto').text('- ' + dinheiro(desconto));
        $('#resumoIva').text(dinheiro(iva));
        $('#resumoMulta').text(dinheiro(multa));

        $('#resumoValorReceber').text(dinheiro(valorReceber));
        $('#resumoValorRecebido').text(dinheiro(valorRecebido));
        $('#resumoDiferenca').text(dinheiro(diferenca));

        if (saldoUtilizado > 0) {
            $('#linhaSaldoUtilizado').show();
            $('#resumoSaldoUtilizado').text(
                '- ' + dinheiro(saldoUtilizado)
            );
        } else {
            $('#linhaSaldoUtilizado').hide();
        }

        /*
         * Esta área só aparece quando o pagamento
         * ultrapassa o valor a receber.
         */
        if (excedente > 0) {

            $('#areaExcedente').show();
            $('#valorExcedente').text(dinheiro(excedente));
            $('#resumoDestinoExcedente').text(dinheiro(excedente));

            const destino = $('input[name="destino_excedente"]:checked').val();

            $('#labelDestinoExcedente').text(
                destino === 'SALDO'
                    ? 'Crédito a adicionar ao aluno'
                    : 'Troco a devolver'
            );

        } else {

            $('#areaExcedente').hide();
        }
    }

    $('#adicionarMeio').off('click.pgPagamento').on('click.pgPagamento', adicionarMeio);

    $(document).off('click.pgPagamento', '.pg-remover-meio')
        .on('click.pgPagamento', '.pg-remover-meio', function () {
            if ($('#listaMeios .pg-payment-row').length === 1) {
                // Mantém sempre um meio visível; permite limpar os seus valores.
                const linha = $(this).closest('.pg-payment-row');
                linha.find('.pg-conta, .pg-meio').val('');
                linha.find('.pg-valor-meio').val('0');
            } else {
                $(this).closest('.pg-payment-row').remove();
            }
            renumerarMeios();
            actualizarResumo();
        });

    $(document).on(
        'change input',
        '.pg-cobranca, .pg-valor-meio, input[name="tipo_desconto"], #percentagem_desconto, input[name="destino_excedente"]',
        function () {

            $('#areaDescontoManual').toggle(
                $('input[name="tipo_desconto"]:checked').val() === 'MANUAL'
            );

            actualizarResumo();
        }
    );

    // Ao abrir a página, apresenta exactamente uma linha.
    $('#listaMeios').empty();
    adicionarMeio();
    actualizarResumo();

    // Intercepta o submit antes de handlers de validação do layout.
    // Não usar form.submit(), pois isso ignora o AJAX.
    const formulario = document.getElementById('formPagamento');
    let pagamentoEmProcessamento = false;
    let pagamentoConcluido = false;

    if (formulario) {
        formulario.addEventListener('submit', async function (evento) {
            evento.preventDefault();
            evento.stopImmediatePropagation();

            if (pagamentoEmProcessamento || pagamentoConcluido) return false;

            if ($('.pg-cobranca:checked').length === 0) {
                await Swal.fire({
                    icon: 'warning', title: 'Cobranças',
                    text: 'Seleccione pelo menos uma cobrança.'
                });
                return false;
            }

            if ($('input[name="tipo_desconto"]:checked').val() === 'MANUAL') {
                await Swal.fire({
                    icon: 'warning', title: 'Desconto manual',
                    text: 'O desconto manual deve ser reflectido na factura antes do pagamento.'
                });
                return false;
            }

            // Multas simuladas ainda não facturadas não podem ser recebidas.
            let temMulta = false;
            $('.pg-cobranca:checked').each(function () {
                if ((Number($(this).attr('data-multa')) || 0) > 0) temMulta = true;
            });
            if (temMulta) {
                await Swal.fire({
                    icon: 'warning', title: 'Multa por facturar',
                    text: 'Existe multa simulada. Registe-a e facture-a antes de confirmar o pagamento.'
                });
                return false;
            }

            let totalMeios = 0;
            let numerario = 0;
            let meiosInvalidos = false;
            $('.pg-payment-row').each(function () {
                const linha = $(this);
                const valor = Number(linha.find('.pg-valor-meio').val());
                const conta = linha.find('.pg-conta').val();
                const meio = linha.find('.pg-meio').val();
                if (!Number.isFinite(valor) || valor < 0) meiosInvalidos = true;
                if (valor > 0) {
                    if (!conta || !meio) meiosInvalidos = true;
                    totalMeios += valor;
                    if (meio === 'NUMERARIO') numerario += valor;
                }
            });
            if (meiosInvalidos) {
                await Swal.fire({
                    icon: 'warning', title: 'Meios de pagamento',
                    text: 'Verifique os valores, as contas financeiras e os meios de pagamento.'
                });
                return false;
            }

            const principal = $('.pg-cobranca:checked').toArray()
                .reduce((soma, el) => soma + (Number($(el).attr('data-principal')) || 0), 0);
            const saldoUsado = Math.min(saldoDisponivel, principal);
            const aReceber = Math.max(0, principal - saldoUsado);
            if (aReceber <= 0 && totalMeios > 0) {
                await Swal.fire({
                    icon: 'warning', title: 'Saldo suficiente',
                    text: 'O saldo do aluno cobre as cobranças. Não é necessário receber dinheiro adicional.'
                });
                return false;
            }
            if (totalMeios <= 0 && saldoUsado <= 0) {
                await Swal.fire({
                    icon: 'warning', title: 'Pagamento sem valor',
                    text: 'Não existe valor recebido nem saldo disponível para liquidar a cobrança.'
                });
                return false;
            }

            const excedente = Math.max(0, totalMeios - aReceber);
            const destino = $('input[name="destino_excedente"]:checked').val() || 'TROCO';
            if (excedente > 0.001 && destino === 'TROCO' && excedente > numerario + 0.001) {
                await Swal.fire({
                    icon: 'warning', title: 'Troco indisponível',
                    text: 'O troco não pode exceder o valor recebido em numerário. Seleccione guardar no saldo do aluno.'
                });
                return false;
            }

            const confirmacao = await Swal.fire({
                icon: 'question', title: 'Confirmar pagamento?',
                html: 'O pagamento será registado e o recibo emitido.<br><strong>Esta operação altera os saldos financeiros.</strong>',
                showCancelButton: true,
                confirmButtonText: 'Sim, confirmar', cancelButtonText: 'Cancelar',
                confirmButtonColor: '#198754',
                allowOutsideClick: false, allowEscapeKey: true
            });
            if (!confirmacao.isConfirmed) return false;

            // Reindexar imediatamente antes do envio impede que duas linhas
            // sejam interpretadas pelo PHP como o mesmo meio de pagamento.
            renumerarMeios();
            pagamentoEmProcessamento = true;
            const botao = $('#confirmarPagamento');
            botao.prop('disabled', true).text('A processar pagamento...');

            console.log(
                'DADOS DO PAGAMENTO:',
                $(formulario).serializeArray()
            );

            try {
                const resposta = await $.ajax({
                    url: formulario.action,
                    type: 'POST',
                    data: $(formulario).serialize(),
                    dataType: 'json',
                    headers: { 'Accept': 'application/json' }
                });

                if (Number(resposta.status) !== 1 || !resposta.recibo_id || !resposta.recibo_url) {
                    throw new Error(resposta.message || 'O servidor não confirmou o pagamento e o recibo.');
                }

                pagamentoConcluido = true;
                const numero = $('<div>').text(resposta.recibo_numero || '').html();
                const resultado = await Swal.fire({
                    icon: 'success', title: 'Pagamento confirmado!',
                    html: 'Pagamento registado com sucesso.<br>Recibo: <strong>' + numero + '</strong>',
                    confirmButtonText: 'Abrir recibo',
                    showCancelButton: true, cancelButtonText: 'Fechar',
                    allowOutsideClick: false, allowEscapeKey: false
                });
                if (resultado.isConfirmed) {
                    window.location.assign(resposta.recibo_url);
                } else {
                    // Após sucesso não permitir reenvio da mesma operação.
                    botao.text('Pagamento já confirmado');
                }
            } catch (erro) {
                let mensagem = 'Não foi possível confirmar o pagamento.';
                if (erro.responseJSON) {
                    if (erro.responseJSON.errors) {
                        mensagem = Object.values(erro.responseJSON.errors).flat().join('\n');
                    } else if (erro.responseJSON.message) {
                        mensagem = erro.responseJSON.message;
                    }
                } else if (erro.message) {
                    mensagem = erro.message;
                }
                await Swal.fire({
                    icon: 'error', title: 'Pagamento não confirmado',
                    text: mensagem, confirmButtonText: 'OK',
                    allowOutsideClick: false
                });
            } finally {
                pagamentoEmProcessamento = false;
                if (!pagamentoConcluido) {
                    botao.prop('disabled', false).html('<i class="fa fa-check"></i> Confirmar pagamento');
                }
            }
            return false;
        }, true);
    }

});

</script>
@endpush
