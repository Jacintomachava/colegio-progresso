<div class="pg-card">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-1">Facturas pendentes</h5>
            <small class="text-muted">
                Facturas emitidas com valores por liquidar
            </small>
        </div>

        <span class="badge bg-warning text-dark">
            {{ $facturasPendentes->count() }} pendente(s)
        </span>
    </div>

    @if($facturasPendentes->isEmpty())

        <div class="alert alert-info">
            Este aluno não possui facturas pendentes.
        </div>

    @else

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">

                <thead>
                    <tr>
                        <th>Factura</th>
                        <th>Emissão</th>
                        <th>Vencimento</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Pago</th>
                        <th class="text-end">Saldo</th>
                        <th>Estado</th>
                        <th>Acções</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($facturasPendentes as $factura)
                        <tr>
                            <td>
                                <strong>{{ $factura->numero }}</strong>
                            </td>

                            <td>
                                {{ \Carbon\Carbon::parse($factura->data_emissao)->format('d/m/Y') }}
                            </td>

                            <td>
                                {{ \Carbon\Carbon::parse($factura->data_vencimento)->format('d/m/Y') }}
                            </td>

                            <td class="text-end">
                                {{ number_format($factura->total, 2, ',', '.') }} MT
                            </td>

                            <td class="text-end">
                                {{ number_format($factura->valor_pago, 2, ',', '.') }} MT
                            </td>

                            <td class="text-end fw-bold text-danger">
                                {{ number_format($factura->saldo_pendente, 2, ',', '.') }} MT
                            </td>

                            <td>
                                @if($factura->estado === 'PARCIAL')
                                    <span class="badge bg-info">Parcial</span>
                                @else
                                    <span class="badge bg-warning text-dark">Pendente</span>
                                @endif
                            </td>

                            <td>
                                <button
                                    type="button"
                                    class="btn btn-success btn-sm btn-pagar-factura"
                                    data-id="{{ $factura->id }}"
                                    data-numero="{{ $factura->numero }}"
                                    data-saldo="{{ $factura->saldo_pendente }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalPagamentoFactura">

                                    <i class="fa fa-money"></i>
                                    Pagar
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>

            </table>
        </div>

    @endif

</div>