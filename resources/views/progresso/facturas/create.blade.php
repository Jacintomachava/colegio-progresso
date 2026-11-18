
@extends('layouts.app')

@section('conteudo')
@php
    $titulos = [
        'MATRICULA' => 'Matrícula e Renovação',
        'FERIAS' => 'Aulas de Férias',
        'PROPINA' => 'Propinas',
        'SERVICO' => 'Serviços',
        'EXTRA' => 'Aulas Extras',
        'OUTROS' => 'Outras Cobranças',
    ];

    $grupos = collect($grupos);
    $temCobrancas = $grupos->sum(fn ($itens) => $itens->count()) > 0;

    $renderGrupo = function ($tipo) use ($grupos, $titulos) {
        return $grupos->get($tipo, collect());
    };
@endphp

<style>
    .factura-card {
        border: 1px solid #e9edf3;
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
    }

    .factura-card .card-header {
        background: #f8fafc;
        border-bottom: 1px solid #edf0f4;
        padding: 14px 16px;
        font-weight: 600;
    }

    .factura-card .card-body {
        padding: 16px;
    }

    .factura-lista {
        max-height: 430px;
        overflow-y: auto;
    }

    .factura-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 10px;
        border-radius: 8px;
        cursor: pointer;
        border: 1px solid transparent;
        margin-bottom: 4px;
    }

    .factura-item:hover {
        background: #f8fafc;
    }

    .factura-item:has(input:checked) {
        background: #eef7f0;
        border-color: #cce8d2;
    }

    .factura-item input {
        margin-top: 4px;
        flex-shrink: 0;
    }

    .factura-item .descricao {
        font-size: 13px;
        line-height: 1.5;
    }

    .factura-item .valor {
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
    }

    .resumo-fixo {
        position: sticky;
        top: 20px;
    }

    .resumo-linha {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 11px;
        font-size: 13px;
    }

    .resumo-total {
        font-size: 21px;
        font-weight: 700;
        color: #198754;
    }

    .factura-titulo {
        font-size: 13px;
        font-weight: 600;
    }

    @media(max-width: 991px) {
        .resumo-fixo {
            position: static;
        }
    }
</style>

<div class="container-fluid py-3">

    <div class="mb-4">
        <h4 class="mb-1">Emissão de Factura</h4>
        <div class="text-muted">
            {{ $aluno->aluno_nome }}
            — {{ $aluno->codigo }}
        </div>
    </div>

    <form id="formFactura">
        @csrf

        <input type="hidden"
               name="aluno_id"
               value="{{ $aluno->id }}">

        <div class="row g-3">

            <div class="col-xl-8">

                @if(!$temCobrancas)
                    <div class="alert alert-info">
                        Este aluno não possui cobranças disponíveis
                        para emissão de factura.
                    </div>
                @endif

                {{-- MATRÍCULA E FÉRIAS --}}
                <div class="row g-3 mb-3">

                    @foreach(['MATRICULA', 'FERIAS'] as $tipo)

                        @if($renderGrupo($tipo)->isNotEmpty())
                            <div class="col-md-6">
                                <div class="factura-card h-100">

                                    <div class="card-header">
                                        {{ $titulos[$tipo] }}
                                    </div>

                                    <div class="card-body factura-lista">

                                        @foreach($renderGrupo($tipo) as $c)
                                            <label class="factura-item">

                                                <input type="checkbox"
                                                    class="form-check-input cobranca"
                                                    name="cobrancas[]"
                                                    value="{{ $c->id }}"
                                                    data-total="{{ $c->valor_total }}"
                                                    data-desconto="{{ $c->valor_desconto }}"
                                                    data-iva="{{ $c->valor_iva }}">

                                                <span class="flex-grow-1 descricao">
                                                    {{ $c->descricao }}
                                                </span>

                                                <span class="valor">
                                                    {{ number_format($c->valor_total, 2, ',', '.') }} MT
                                                </span>

                                            </label>
                                        @endforeach

                                    </div>
                                </div>
                            </div>
                        @endif

                    @endforeach

                </div>

                {{-- PROPINA, SERVIÇOS E EXTRAS --}}
                <div class="row g-3">

                    @foreach(['PROPINA', 'SERVICO', 'EXTRA'] as $tipo)

                        @if($renderGrupo($tipo)->isNotEmpty())
                            <div class="col-md-6 col-xl-4">
                                <div class="factura-card h-100">

                                    <div class="card-header d-flex justify-content-between">
                                        <span>{{ $titulos[$tipo] }}</span>

                                        <span class="badge bg-secondary">
                                            {{ $renderGrupo($tipo)->count() }}
                                        </span>
                                    </div>

                                    <div class="card-body factura-lista">

                                        @foreach($renderGrupo($tipo) as $c)
                                            <label class="factura-item">

                                                <input type="checkbox"
                                                    class="form-check-input cobranca"
                                                    name="cobrancas[]"
                                                    value="{{ $c->id }}"
                                                    data-total="{{ $c->valor_total }}"
                                                    data-desconto="{{ $c->valor_desconto }}"
                                                    data-iva="{{ $c->valor_iva }}">

                                                <span class="flex-grow-1 descricao">
                                                    {{ $c->descricao }}
                                                </span>

                                                <span class="valor">
                                                    {{ number_format($c->valor_total, 2, ',', '.') }} MT
                                                </span>

                                            </label>
                                        @endforeach

                                    </div>
                                </div>
                            </div>
                        @endif

                    @endforeach

                </div>

                {{-- OUTRAS COBRANÇAS --}}
                @if($renderGrupo('OUTROS')->isNotEmpty())
                    <div class="factura-card mt-3">
                        <div class="card-header">
                            Outras Cobranças
                        </div>

                        <div class="card-body">
                            @foreach($renderGrupo('OUTROS') as $c)
                                <label class="factura-item">

                                    <input type="checkbox"
                                        class="form-check-input cobranca"
                                        name="cobrancas[]"
                                        value="{{ $c->id }}"
                                        data-total="{{ $c->valor_total }}"
                                        data-desconto="{{ $c->valor_desconto }}"
                                        data-iva="{{ $c->valor_iva }}">

                                    <span class="flex-grow-1 descricao">
                                        {{ $c->descricao }}
                                    </span>

                                    <span class="valor">
                                        {{ number_format($c->valor_total, 2, ',', '.') }} MT
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>

            {{-- RESUMO FINANCEIRO --}}
            <div class="col-xl-4">
                <div class="factura-card resumo-fixo">

                    <div class="card-header">
                        Resumo Financeiro
                    </div>

                    <div class="card-body">

                        <div class="resumo-linha">
                            <span>Itens seleccionados</span>
                            <strong id="qtdItens">0</strong>
                        </div>

                        <div class="resumo-linha">
                            <span>Subtotal</span>
                            <strong id="subtotal">0,00 MT</strong>
                        </div>

                        <div class="resumo-linha">
                            <span>Desconto automático</span>
                            <strong id="descontoAutomatico">0,00 MT</strong>
                        </div>

                        <div class="resumo-linha">
                            <span>IVA</span>
                            <strong id="iva">0,00 MT</strong>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Desconto manual
                            </label>

                            <div class="row g-2">

                                <div class="col-5">
                                    <select name="tipo_desconto_manual"
                                            id="tipoDesconto"
                                            class="form-select">
                                        <option value="PERCENTUAL">%</option>
                                        <option value="VALOR">MT</option>
                                    </select>
                                </div>

                                <div class="col-7">
                                    <input type="number"
                                           name="valor_desconto_manual"
                                           id="valorDesconto"
                                           class="form-control"
                                           min="0"
                                           step="0.01"
                                           value="0">
                                </div>

                            </div>

                            <small class="text-muted">
                                O desconto manual substitui o automático e é calculado sobre o subtotal original.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Motivo do desconto
                            </label>

                            <textarea name="motivo_desconto"
                                      id="motivoDesconto"
                                      class="form-control"
                                      rows="2"
                                      maxlength="500"
                                      placeholder="Obrigatório quando houver desconto manual"></textarea>
                        </div>

                        <div class="resumo-linha">
                            <span>Desconto manual</span>
                            <strong id="descontoManual">0,00 MT</strong>
                        </div>

                        <hr>

                        <div class="resumo-linha align-items-center">
                            <strong>Total a facturar</strong>
                            <span class="resumo-total" id="total">
                                0,00 MT
                            </span>
                        </div>

                        <div class="mb-3 mt-3">
                            <label class="form-label">Observações</label>

                            <textarea name="observacoes"
                                      class="form-control"
                                      rows="2"
                                      maxlength="1000"
                                      placeholder="Observações opcionais"></textarea>
                        </div>

                        <button type="submit"
                                id="btnEmitir"
                                class="btn btn-success w-100"
                                @disabled(!$temCobrancas)>
                            Emitir Factura
                        </button>

                        <small class="d-block text-muted mt-3">
                            A emissão da factura não regista pagamento.
                            Os valores são obtidos das cobranças existentes.
                        </small>

                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection

@push('js')



<script>
(function () {
    'use strict';

    function iniciarFormularioFactura() {

        const $form = $('#formFactura');

        if (!$form.length || $form.data('factura-inicializada')) {
            return;
        }

        $form.data('factura-inicializada', true);

        let emProcessamento = false;
        let facturaEmitida = false;

        const $botao = $('#btnEmitir');

        // IVA configurado na escola.
        // Exemplo: escolas.iva = 16 significa 16%.
        const ivaPercentagem = Number(@json($ivaEscola ?? 0));

        const centavos = valor =>
            Math.round(Number(valor || 0) * 100);

        const dinheiro = cent =>
            new Intl.NumberFormat('pt-MZ', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(cent / 100) + ' MT';

        function actualizarResumo() {

            let totalCobrancas = 0;
            let descontoAutomatico = 0;
            let ivaOriginal = 0;
            let quantidade = 0;

            // Somar as cobranças seleccionadas.
            $form.find('.cobranca:checked').each(function () {

                totalCobrancas += centavos(
                    $(this).attr('data-total')
                );

                descontoAutomatico += centavos(
                    $(this).attr('data-desconto')
                );

                ivaOriginal += centavos(
                    $(this).attr('data-iva')
                );

                quantidade++;
            });

            /*
             * SUBTOTAL ORIGINAL
             *
             * valor_total = original - desconto + IVA
             *
             * Recuperamos o valor antes do desconto
             * e antes do IVA já existente na cobrança.
             */
            const subtotal = Math.max(
                0,
                totalCobrancas + descontoAutomatico - ivaOriginal
            );

            /*
             * DESCONTO
             *
             * Manual substitui o automático.
             */
            const tipo = $('#tipoDesconto').val();

            const valorInformado = Number(
                $('#valorDesconto').val() || 0
            );

            const temManual =
                Number.isFinite(valorInformado) &&
                valorInformado > 0;

            let descontoAplicado = descontoAutomatico;

            if (temManual) {

                if (tipo === 'PERCENTUAL') {

                    descontoAplicado = Math.round(
                        subtotal *
                        Math.min(valorInformado, 100) / 100
                    );

                } else {

                    descontoAplicado = Math.min(
                        centavos(valorInformado),
                        subtotal
                    );
                }
            }

            /*
             * IVA DA ESCOLA
             *
             * Calculado depois do desconto.
             */
            const baseTributavel = Math.max(
                0,
                subtotal - descontoAplicado
            );

            const ivaCalculado = Math.round(
                baseTributavel * ivaPercentagem / 100
            );

            const totalFinal =
                baseTributavel + ivaCalculado;

            /*
             * ACTUALIZAR RESUMO
             */
            $('#qtdItens').text(quantidade);

            $('#subtotal').text(
                dinheiro(subtotal)
            );

            $('#descontoAutomatico').text(
                dinheiro(
                    temManual ? 0 : descontoAutomatico
                )
            );

            $('#descontoManual').text(
                dinheiro(
                    temManual ? descontoAplicado : 0
                )
            );

            $('#iva').text(
                dinheiro(ivaCalculado)
            );

            $('#total').text(
                dinheiro(totalFinal)
            );

            return {
                quantidade,
                temManual,
                valorInformado,
                descontoManual: temManual
                    ? descontoAplicado
                    : 0,
                subtotal,
                iva: ivaCalculado,
                totalFinal
            };
        }

        /*
         * ACTUALIZAR AO SELECCIONAR COBRANÇAS
         */
        $form.on(
            'change.progressoFactura',
            '.cobranca, #tipoDesconto',
            actualizarResumo
        );

        /*
         * ACTUALIZAR AO INTRODUZIR DESCONTO
         */
        $form.on(
            'input.progressoFactura',
            '#valorDesconto',
            actualizarResumo
        );

        /*
         * SUBMISSÃO AJAX
         */
        $form.on(
            'submit.progressoFactura',
            function (e) {

                e.preventDefault();
                e.stopImmediatePropagation();

                if (emProcessamento || facturaEmitida) {
                    return false;
                }

                const resumo = actualizarResumo();

                /*
                 * VALIDAÇÕES
                 */
                if (!resumo.quantidade) {

                    Swal.fire(
                        'Atenção',
                        'Seleccione pelo menos uma cobrança.',
                        'warning'
                    );

                    return false;
                }

                if (
                    !Number.isFinite(resumo.valorInformado) ||
                    resumo.valorInformado < 0 ||
                    (
                        $('#tipoDesconto').val() === 'PERCENTUAL' &&
                        resumo.valorInformado > 100
                    )
                ) {

                    Swal.fire(
                        'Atenção',
                        'Informe um desconto válido.',
                        'warning'
                    );

                    return false;
                }

                if (
                    resumo.temManual &&
                    !$('#motivoDesconto').val().trim()
                ) {

                    Swal.fire(
                        'Atenção',
                        'Informe o motivo do desconto manual.',
                        'warning'
                    );

                    return false;
                }

                if (
                    resumo.temManual &&
                    $('#tipoDesconto').val() === 'VALOR' &&
                    centavos(resumo.valorInformado) > resumo.subtotal
                ) {

                    Swal.fire(
                        'Atenção',
                        'O desconto não pode ultrapassar o subtotal.',
                        'warning'
                    );

                    return false;
                }

                /*
                 * BLOQUEAR SUBMISSÃO DUPLICADA
                 */
                emProcessamento = true;

                $botao
                    .prop('disabled', true)
                    .text('A emitir...');

                /*
                 * ENVIAR FACTURA
                 */
                $.ajax({

                    url: @json(route('progresso.facturas.store')),

                    method: 'POST',

                    data: $form.serialize(),

                    dataType: 'json',

                    success: function (res) {

                        if (Number(res.status) !== 1) {

                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: res.message ||
                                    'Não foi possível emitir a factura.'
                            });

                            return;
                        }

                        /*
                         * FACTURA EMITIDA
                         */
                        facturaEmitida = true;

                        $botao.text('Factura emitida');

                        const totalFormatado =
                            new Intl.NumberFormat('pt-MZ', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }).format(
                                Number(res.factura_total || 0)
                            );

                        /*
                         * SWEETALERT
                         */
                        Swal.fire({

                            icon: 'success',

                            title: 'Factura emitida com sucesso!',

                            html: `
                                <p>
                                    A factura foi registada com sucesso.
                                </p>

                                <h5 class="text-success">
                                    ${res.factura_numero || ''}
                                </h5>

                                <p>
                                    <strong>Total:</strong>
                                    ${totalFormatado} MT
                                </p>
                            `,

                            showCancelButton: true,

                            confirmButtonText:
                                '<i class="fas fa-print"></i> Imprimir Factura',

                            cancelButtonText: 'OK',

                            confirmButtonColor: '#198754',

                            cancelButtonColor: '#6c757d',

                            allowOutsideClick: false,

                            allowEscapeKey: false,

                            showCloseButton: false,

                            timer: undefined

                        }).then(function (resultado) {

                            if (
                                resultado.isConfirmed &&
                                res.factura_url
                            ) {

                                window.open(
                                    res.factura_url,
                                    '_blank'
                                );
                            }

                            /*
                             * REDIRECCIONAR APENAS
                             * DEPOIS DA ESCOLHA
                             */
                            window.location.assign(
                                @json(route('progresso.facturas.index'))
                            );
                        });
                    },

                    /*
                     * ERRO AJAX
                     */
                    error: function (xhr) {

                        let mensagem =
                            xhr.responseJSON?.message ||
                            'Não foi possível emitir a factura.';

                        if (xhr.responseJSON?.errors) {

                            mensagem = Object.values(
                                xhr.responseJSON.errors
                            ).flat().join('\n');
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Erro ao emitir factura',
                            text: mensagem
                        });
                    },

                    /*
                     * FINALIZAR REQUISIÇÃO
                     */
                    complete: function () {

                        emProcessamento = false;

                        if (!facturaEmitida) {

                            $botao
                                .prop('disabled', false)
                                .text('Emitir Factura');
                        }
                    }
                });

                return false;
            }
        );

        /*
         * RESUMO INICIAL
         */
        actualizarResumo();
    }

    /*
     * INICIALIZAÇÃO
     */
    if (document.readyState === 'loading') {

        document.addEventListener(
            'DOMContentLoaded',
            iniciarFormularioFactura,
            { once: true }
        );

    } else {

        iniciarFormularioFactura();
    }

})();
</script>


@endpush
