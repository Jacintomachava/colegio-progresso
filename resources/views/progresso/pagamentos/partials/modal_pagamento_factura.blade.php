<div class="modal fade" id="modalPagamentoFactura" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    Pagar factura
                    <span id="modalFacturaNumero"></span>
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"></button>
            </div>

            <form id="formPagamentoFactura"
                  action="{{ route('progresso.pagamentos.store') }}"
                  method="POST">

                @csrf

                <input type="hidden"
                       name="aluno_id"
                       value="{{ $aluno->id }}">

                <input type="hidden"
                       name="factura_id"
                       id="modalFacturaId">

                <input type="hidden"
                       name="tipo_desconto"
                       value="AUTOMATICO">

                <input type="hidden"
                       name="destino_excedente"
                       value="TROCO">

                <input type="hidden"
                       name="chave_idempotencia"
                       id="modalChaveIdempotencia"
                       value="{{ (string) \Illuminate\Support\Str::uuid() }}">

                <div class="modal-body">

                    <div class="alert alert-info">
                        Saldo pendente:
                        <strong id="modalFacturaSaldo"></strong>
                    </div>

                    <div id="modalFacturaCobrancas"></div>

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">
                                Conta financeira
                            </label>

                            <select class="form-select"
                                    name="meios[0][conta_financeira_id]"
                                    required>
                                <option value="">Seleccionar</option>

                                @foreach($contas as $conta)
                                    <option value="{{ $conta->id }}">
                                        {{ $conta->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Meio de pagamento
                            </label>

                            <select class="form-select"
                                    name="meios[0][meio]"
                                    required>
                                <option value="">Seleccionar</option>
                                <option value="NUMERARIO">Numerário</option>
                                <option value="DEPOSITO">Depósito</option>
                                <option value="TRANSFERENCIA">Transferência</option>
                                <option value="MPESA">M-Pesa</option>
                                <option value="EMOLA">e-Mola</option>
                                <option value="MKESH">mKesh</option>
                                <option value="CARTAO">Cartão</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Valor recebido
                            </label>

                            <input type="number"
                                   class="form-control"
                                   name="meios[0][valor]"
                                   id="modalValorRecebido"
                                   min="0.01"
                                   step="0.01"
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Referência (opcional)
                            </label>

                            <input type="text"
                                   class="form-control"
                                   name="meios[0][referencia]">
                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit"
                            class="btn btn-success"
                            id="btnConfirmarFactura">
                        Confirmar pagamento
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>