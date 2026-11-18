@extends('layouts.app')

@section('conteudo')
<div class="container-fluid py-3">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Gestão de Actividades</h4>
            <small class="text-muted">
                Serviços, aulas extras e cursos de férias
            </small>
        </div>

        <button type="button"
                class="btn btn-primary"
                id="btnNovaActividade">
            <i class="fas fa-plus me-1"></i>
            Nova Actividade
        </button>
    </div>

    <div class="card shadow-sm">
        <div class="card-body table-responsive">

            <table class="table table-bordered table-hover align-middle"
                   id="tabelaActividades">

                <thead class="table-light">
                    <tr>
                        <th>Actividade</th>
                        <th>Tipo</th>
                        <th>Descrição</th>
                        <th class="text-end">Valor (MT)</th>
                        <th>Periodicidade</th>
                        <th>Estado</th>
                        <th class="text-center">Acção</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($actividades as $actividade)
                        <tr>
                            <td>{{ $actividade->nome }}</td>

                            <td>
                                @switch($actividade->tipo)
                                    @case('SERVICO')
                                        <span class="badge bg-primary">
                                            Serviço
                                        </span>
                                        @break

                                    @case('AULA_EXTRA')
                                        <span class="badge bg-info text-dark">
                                            Aula extra
                                        </span>
                                        @break

                                    @case('CURSO_FERIAS')
                                        <span class="badge bg-warning text-dark">
                                            Curso de férias
                                        </span>
                                        @break
                                @endswitch
                            </td>

                            <td>
                                {{ $actividade->descricao ?: '-' }}
                            </td>

                            <td class="text-end">
                                {{ number_format(
                                    $actividade->valor_mensal,
                                    2,
                                    ',',
                                    '.'
                                ) }}
                            </td>

                            <td>
                                @if($actividade->periodicidade === 'MENSAL')
                                    <span class="badge bg-secondary">
                                        Mensal
                                    </span>
                                @else
                                    <span class="badge bg-dark">
                                        Única
                                    </span>
                                @endif
                            </td>

                            <td>
                                <span class="badge {{
                                    $actividade->estado === 'ACTIVO'
                                        ? 'bg-success'
                                        : 'bg-secondary'
                                }}">
                                    {{ $actividade->estado }}
                                </span>
                            </td>

                            <td class="text-center">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary btnEditar"
                                    data-id="{{ $actividade->id }}"
                                    data-nome="{{ $actividade->nome }}"
                                    data-tipo="{{ $actividade->tipo }}"
                                    data-descricao="{{ $actividade->descricao }}"
                                    data-valor="{{ $actividade->valor_mensal }}"
                                    data-periodicidade="{{ $actividade->periodicidade }}"
                                    data-estado="{{ $actividade->estado }}">

                                    <i class="fas fa-edit"></i>
                                    Editar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7"
                                class="text-center text-muted">
                                Nenhuma actividade registada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>

        </div>
    </div>
</div>

{{-- MODAL REGISTAR / EDITAR --}}
<div class="modal fade"
     id="modalActividade"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <form id="formActividade">
                @csrf

                <input type="hidden"
                       name="id"
                       id="actividade_id">

                <div class="modal-header">
                    <h5 class="modal-title" id="tituloModal">
                        Nova Actividade
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Fechar">
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-md-8">
                            <label class="form-label">
                                Nome da actividade *
                            </label>

                            <input
                                type="text"
                                name="nome"
                                id="actividade_nome"
                                class="form-control"
                                maxlength="150"
                                required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                Tipo *
                            </label>

                            <select
                                name="tipo"
                                id="actividade_tipo"
                                class="form-select"
                                required>

                                <option value="">Seleccione</option>
                                <option value="SERVICO">Serviço</option>
                                <option value="AULA_EXTRA">Aula extra</option>
                                <option value="CURSO_FERIAS">Curso de férias</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Periodicidade *
                            </label>

                            <select
                                name="periodicidade"
                                id="actividade_periodicidade"
                                class="form-select"
                                required>

                                <option value="MENSAL">Mensal</option>
                                <option value="UNICA">Única</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Valor da actividade (MT) *
                            </label>

                            <input
                                type="number"
                                name="valor_mensal"
                                id="actividade_valor"
                                class="form-control"
                                min="0"
                                step="0.01"
                                required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Estado *
                            </label>

                            <select
                                name="estado"
                                id="actividade_estado"
                                class="form-select"
                                required>

                                <option value="ACTIVO">ACTIVO</option>
                                <option value="INACTIVO">INACTIVO</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">
                                Descrição
                            </label>

                            <textarea
                                name="descricao"
                                id="actividade_descricao"
                                class="form-control"
                                rows="3"
                                placeholder="Descrição da actividade"></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button
                        type="submit"
                        class="btn btn-primary"
                        id="btnGuardar">

                        <i class="fas fa-save me-1"></i>
                        Guardar
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

    const modalEl = document.getElementById('modalActividade');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

    const $form = $('#formActividade');
    const $guardar = $('#btnGuardar');

    const urlStore = @json(route('progresso.actividades.store'));

    const urlUpdate = @json(
        route('progresso.actividades.update', ['id' => '__ID__'])
    );

    let editarId = null;

    /*
    |--------------------------------------------------------------------------
    | NOVA ACTIVIDADE
    |--------------------------------------------------------------------------
    */
    $('#btnNovaActividade').on('click', function () {

        editarId = null;

        $form[0].reset();

        $('#actividade_id').val('');
        $('#tituloModal').text('Nova Actividade');

        modal.show();
    });

    /*
    |--------------------------------------------------------------------------
    | EDITAR ACTIVIDADE
    |--------------------------------------------------------------------------
    */
    $(document).on('click', '.btnEditar', function () {

        const botao = $(this);

        editarId = botao.attr('data-id');

        $form[0].reset();

        $('#actividade_id').val(editarId);

        $('#actividade_nome').val(
            botao.attr('data-nome')
        );

        $('#actividade_tipo').val(
            botao.attr('data-tipo')
        );

        $('#actividade_descricao').val(
            botao.attr('data-descricao')
        );

        $('#actividade_valor').val(
            botao.attr('data-valor')
        );

        $('#actividade_periodicidade').val(
            botao.attr('data-periodicidade')
        );

        $('#actividade_estado').val(
            botao.attr('data-estado')
        );

        $('#tituloModal').text('Editar Actividade');

        modal.show();
    });

    /*
    |--------------------------------------------------------------------------
    | GUARDAR / ACTUALIZAR
    |--------------------------------------------------------------------------
    */
    $form.on('submit', function (event) {

        event.preventDefault();

        if ($guardar.prop('disabled')) {
            return;
        }

        if (!this.checkValidity()) {
            this.reportValidity();
            return;
        }

        const dados = $form.serializeArray();

        let url = urlStore;

        if (editarId) {

            url = urlUpdate.replace(
                '__ID__',
                encodeURIComponent(editarId)
            );

            dados.push({
                name: '_method',
                value: 'PUT'
            });
        }

        $guardar
            .prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin me-1"></i> A guardar...');

        $.ajax({

            url: url,
            type: 'POST',
            data: $.param(dados),
            dataType: 'json',

            headers: {
                'Accept': 'application/json'
            },

            success: function (res) {

                if (Number(res.status) !== 1) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: res.message || 'Operação não confirmada.'
                    });

                    return;
                }

                modal.hide();

                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso!',
                    text: res.message || 'Operação realizada com sucesso.',
                    confirmButtonText: 'OK'
                }).then(function () {

                    window.location.reload();

                });
            },

            error: function (xhr) {

                const errors = xhr.responseJSON?.errors;

                const mensagem = errors
                    ? Object.values(errors).flat().join('\n')
                    : (
                        xhr.responseJSON?.message ||
                        'Não foi possível guardar a actividade.'
                    );

                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: mensagem
                });
            },

            complete: function () {

                $guardar
                    .prop('disabled', false)
                    .html('<i class="fas fa-save me-1"></i> Guardar');
            }

        });

    });

});
</script>
@endpush