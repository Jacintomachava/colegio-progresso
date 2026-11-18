@extends('layouts.app')

@section('conteudo')
<div class="container-fluid py-3">

    <div class="mb-3">
        <h4 class="mb-0">Valores por Classe</h4>
        <small class="text-muted">
            Gestão dos valores de matrícula, renovação e propina por ano académico
        </small>
    </div>

    <div class="card shadow-sm">
        <div class="card-body table-responsive">

            <table class="table table-bordered table-hover align-middle"
                   id="tabelaValores">

                <thead class="table-light">
                    <tr>
                        <th>Classe</th>
                        <th class="text-end">Matrícula (MT)</th>
                        <th class="text-end">Renovação (MT)</th>
                        <th class="text-end">Propina (MT)</th>
                        <th>Estado</th>
                        <th class="text-center">Acção</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($valores as $valor)
                        <tr>

                            <td>{{ $valor->classe_nome }}</td>

                            <td class="text-end">
                                {{ number_format($valor->valor_matricula, 2, ',', '.') }}
                            </td>

                            <td class="text-end">
                                {{ number_format($valor->valor_renovacao, 2, ',', '.') }}
                            </td>

                            <td class="text-end">
                                {{ number_format($valor->valor_propina, 2, ',', '.') }}
                            </td>

                            <td>
                                <span class="badge {{ $valor->estado === 'ACTIVO' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $valor->estado }}
                                </span>
                            </td>

                            <td class="text-center">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary btnEditar"
                                    data-id="{{ $valor->id }}"
                                    data-ano="{{ $valor->ano_academico_id }}"
                                    data-classe="{{ $valor->classe_id }}"
                                    data-matricula="{{ $valor->valor_matricula }}"
                                    data-renovacao="{{ $valor->valor_renovacao }}"
                                    data-propina="{{ $valor->valor_propina }}">

                                    <i class="fas fa-edit"></i>
                                    Editar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                Nenhum valor configurado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>

        </div>
    </div>

</div>

{{-- MODAL DE EDIÇÃO --}}
<div class="modal fade"
     id="modalValores"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <form id="formValores">
                @csrf
                @method('PUT')

                <input type="hidden" name="id" id="valor_id">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>
                        Editar valores da classe
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Fechar">
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">
                                Ano académico
                            </label>

                            <select name="ano_academico_id"
                                    id="ano_academico_id"
                                    class="form-select"
                                    disabled>

                                @foreach($anos as $ano)
                                    <option value="{{ $ano->id }}">
                                        {{ $ano->ano }}
                                    </option>
                                @endforeach

                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Classe
                            </label>

                            <select name="classe_id"
                                    id="classe_id"
                                    class="form-select"
                                    disabled>

                                @foreach($classes as $classe)
                                    <option value="{{ $classe->id }}">
                                        {{ $classe->nome }}
                                    </option>
                                @endforeach

                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                Valor matrícula (MT) *
                            </label>

                            <input type="number"
                                   name="valor_matricula"
                                   id="valor_matricula"
                                   class="form-control"
                                   min="0"
                                   step="0.01"
                                   required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                Valor renovação (MT) *
                            </label>

                            <input type="number"
                                   name="valor_renovacao"
                                   id="valor_renovacao"
                                   class="form-control"
                                   min="0"
                                   step="0.01"
                                   required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                Valor propina (MT) *
                            </label>

                            <input type="number"
                                   name="valor_propina"
                                   id="valor_propina"
                                   class="form-control"
                                   min="0"
                                   step="0.01"
                                   required>
                        </div>

                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Os novos valores serão utilizados nas próximas
                        cobranças. Facturas já emitidas não serão alteradas.
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit"
                            id="btnGuardar"
                            class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        Guardar alterações
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
@endsection

@push('js')
<script>
$(function () {

    const modalEl = document.getElementById('modalValores');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

    const $form = $('#formValores');
    const $guardar = $('#btnGuardar');

    const urlUpdateBase = @json(url('/progresso/valores-classes'));

    let editarId = null;

    // ABRIR MODAL DE EDIÇÃO
    $(document).on('click', '.btnEditar', function () {

        const botao = $(this);

        editarId = botao.attr('data-id');

        $form[0].reset();

        $('#valor_id').val(editarId);

        $('#ano_academico_id').val(botao.attr('data-ano'));
        $('#classe_id').val(botao.attr('data-classe'));

        $('#valor_matricula').val(botao.attr('data-matricula'));
        $('#valor_renovacao').val(botao.attr('data-renovacao'));
        $('#valor_propina').val(botao.attr('data-propina'));

        modal.show();
    });

    // GUARDAR ALTERAÇÕES
    $form.on('submit', function (event) {

        event.preventDefault();

        if (!editarId || $guardar.prop('disabled')) {
            return;
        }

        if (!this.checkValidity()) {
            this.reportValidity();
            return;
        }

        $guardar
            .prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin me-1"></i> A guardar...');

        $.ajax({

            url: urlUpdateBase + '/' + encodeURIComponent(editarId),

            type: 'POST',

            data: $form.serialize(),

            dataType: 'json',

            headers: {
                'Accept': 'application/json'
            },

            success: function (res) {

                modal.hide();

                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso!',
                    text: res.message || 'Valores actualizados com sucesso.',
                    confirmButtonText: 'OK'
                }).then(function () {
                    window.location.reload();
                });
            },

            error: function (xhr) {

                const errors = xhr.responseJSON?.errors;

                const mensagem = errors
                    ? Object.values(errors).flat().join('\n')
                    : (xhr.responseJSON?.message ||
                       'Não foi possível actualizar os valores.');

                Swal.fire({
                    icon: 'error',
                    title: 'Erro ao actualizar',
                    text: mensagem
                });
            },

            complete: function () {

                $guardar
                    .prop('disabled', false)
                    .html('<i class="fas fa-save me-1"></i> Guardar alterações');
            }

        });

    });

});
</script>
@endpush