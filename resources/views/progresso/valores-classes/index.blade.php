@extends('layouts.app')

@section('conteudo')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div><h4 class="mb-0">Valores por Classe</h4><small class="text-muted">Matrícula, renovação e propina por ano académico</small></div>
        <button type="button" class="btn btn-primary" id="btnNovo"><i class="fas fa-plus"></i> Novo Valor</button>
    </div>

    <div class="card shadow-sm"><div class="card-body table-responsive">
        <table class="table table-bordered table-hover align-middle" id="tabelaValores">
            <thead class="table-light"><tr>
                <th>Classe</th><th>Matrícula (MT)</th><th>Renovação (MT)</th><th>Propina (MT)</th><th>Estado</th><th>Acção</th>
            </tr></thead>
            <tbody>
            @forelse($valores as $valor)
                <tr>
                    <td>{{ $valor->classe_nome }}</td>
                    <td>{{ number_format($valor->valor_matricula, 2, ',', '.') }}</td>
                    <td>{{ number_format($valor->valor_renovacao, 2, ',', '.') }}</td>
                    <td>{{ number_format($valor->valor_propina, 2, ',', '.') }}</td>
                    <td><span class="badge {{ $valor->estado === 'ACTIVO' ? 'bg-success' : 'bg-secondary' }}">{{ $valor->estado }}</span></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-primary btnEditar"
                            data-id="{{ $valor->id }}"
                            data-ano="{{ $valor->ano_academico_id }}"
                            data-classe="{{ $valor->classe_id }}"
                            data-matricula="{{ $valor->valor_matricula }}"
                            data-renovacao="{{ $valor->valor_renovacao }}"
                            data-propina="{{ $valor->valor_propina }}"
                            data-inicio="{{ $valor->mes_inicio }}"
                            data-fim="{{ $valor->mes_fim }}"
                            data-estado="{{ $valor->estado }}">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted">Nenhum valor configurado.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div></div>
</div>

<div class="modal fade" id="modalValores" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
        <form id="formValores">
            @csrf
            <div class="modal-header"><h5 class="modal-title" id="tituloModal">Novo Valor</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Ano académico *</label>
                        <select name="ano_academico_id" class="form-select" required><option value="">Seleccione</option>@foreach($anos as $ano)<option value="{{ $ano->id }}">{{ $ano->ano }}</option>@endforeach</select>
                    </div>
                    <div class="col-md-6"><label class="form-label">Classe *</label>
                        <select name="classe_id" class="form-select" required><option value="">Seleccione</option>@foreach($classes as $classe)<option value="{{ $classe->id }}">{{ $classe->nome }}</option>@endforeach</select>
                    </div>
                    <div class="col-md-4"><label class="form-label">Valor matrícula (MT) *</label><input type="number" name="valor_matricula" class="form-control" min="0" step="0.01" required></div>
                    <div class="col-md-4"><label class="form-label">Valor renovação (MT) *</label><input type="number" name="valor_renovacao" class="form-control" min="0" step="0.01" required></div>
                    <div class="col-md-4"><label class="form-label">Valor propina (MT) *</label><input type="number" name="valor_propina" class="form-control" min="0" step="0.01" required></div>
                    <div class="col-md-4"><label class="form-label">Mês início *</label><select name="mes_inicio" class="form-select" required>@for($i=1;$i<=12;$i++)<option value="{{ $i }}">{{ $i }}</option>@endfor</select></div>
                    <div class="col-md-4"><label class="form-label">Mês fim *</label><select name="mes_fim" class="form-select" required>@for($i=1;$i<=12;$i++)<option value="{{ $i }}" @selected($i===12)>{{ $i }}</option>@endfor</select></div>
                    <div class="col-md-4"><label class="form-label">Estado *</label><select name="estado" class="form-select" required><option value="ACTIVO">ACTIVO</option><option value="INACTIVO">INACTIVO</option></select></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" id="btnGuardar" class="btn btn-primary">Guardar</button></div>
        </form>
    </div></div>
</div>
@endsection

@push('js')
<script>
$(function () {
    const modalEl = document.getElementById('modalValores');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    const $form = $('#formValores');
    const $guardar = $('#btnGuardar');
    const urlStore = @json(route('progresso.valores-classes.store'));
    const urlUpdateBase = @json(url('/progresso/valores-classes'));
    let editarId = null;

    $('#btnNovo').on('click', function () {
        editarId = null;
        $form[0].reset();
        $('#tituloModal').text('Novo Valor');
        modal.show();
    });

    $('.btnEditar').on('click', function () {
        const d = $(this).data();
        editarId = d.id;
        $form[0].reset();
        $form.find('[name="ano_academico_id"]').val(d.ano);
        $form.find('[name="classe_id"]').val(d.classe);
        $form.find('[name="valor_matricula"]').val(d.matricula);
        $form.find('[name="valor_renovacao"]').val(d.renovacao);
        $form.find('[name="valor_propina"]').val(d.propina);
        $form.find('[name="mes_inicio"]').val(d.inicio);
        $form.find('[name="mes_fim"]').val(d.fim);
        $form.find('[name="estado"]').val(d.estado);
        $('#tituloModal').text('Editar Valor');
        modal.show();
    });

    $form.on('submit', function (event) {
        event.preventDefault();
        if ($guardar.prop('disabled')) return;
        const inicio = Number($form.find('[name="mes_inicio"]').val());
        const fim = Number($form.find('[name="mes_fim"]').val());
        if (inicio > fim) {
            Swal.fire('Atenção', 'O mês inicial não pode ser superior ao mês final.', 'warning');
            return;
        }
        $guardar.prop('disabled', true).text('A guardar...');
        $.ajax({
            url: editarId ? urlUpdateBase + '/' + editarId : urlStore,
            type: editarId ? 'PUT' : 'POST',
            data: $form.serialize(),
            success: function (res) {
                modal.hide();
                Swal.fire({icon:'success', title:'Sucesso', text:res.message}).then(() => location.reload());
            },
            error: function (xhr) {
                const errors = xhr.responseJSON?.errors;
                const msg = errors ? Object.values(errors).flat().join('\n') : (xhr.responseJSON?.message || 'Não foi possível guardar os valores.');
                Swal.fire({icon:'error', title:'Erro', text:msg});
            },
            complete: function () { $guardar.prop('disabled', false).text('Guardar'); }
        });
    });
});
</script>
@endpush
