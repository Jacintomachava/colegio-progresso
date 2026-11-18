
@extends('layouts.app')

@section('conteudo')

<div class="container-fluid">

    <div class="row">
        <div class="col-12">

            <div class="card">

                <div class="card-header">
                    <h4>Gestão de Recibos</h4>
                    <p class="text-muted mb-0">
                        Consulta e reimpressão de recibos emitidos
                    </p>
                </div>

                <div class="card-body">

                    {{-- FILTROS --}}
                    <form method="GET"
                          action="{{ route('progresso.recibos.index') }}"
                          class="row g-3 mb-4">

                        <div class="col-md-4">
                            <label class="form-label">
                                Pesquisar
                            </label>

                            <input type="text"
                                   name="pesquisa"
                                   class="form-control"
                                   value="{{ request('pesquisa') }}"
                                   placeholder="Recibo, nome ou código do aluno">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">
                                Data inicial
                            </label>

                            <input type="date"
                                   name="data_inicio"
                                   class="form-control"
                                   value="{{ request('data_inicio') }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">
                                Data final
                            </label>

                            <input type="date"
                                   name="data_fim"
                                   class="form-control"
                                   value="{{ request('data_fim') }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">
                                Estado
                            </label>

                            <select name="estado"
                                    class="form-select">
                                <option value="">Todos</option>

                                <option value="EMITIDO"
                                    @selected(request('estado') === 'EMITIDO')>
                                    Emitido
                                </option>

                                <option value="ANULADO"
                                    @selected(request('estado') === 'ANULADO')>
                                    Anulado
                                </option>
                            </select>
                        </div>

                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit"
                                    class="btn btn-primary">
                                <i class="fa fa-search"></i>
                                Filtrar
                            </button>

                            <a href="{{ route('progresso.recibos.index') }}"
                               class="btn btn-outline-secondary">
                                Limpar
                            </a>
                        </div>

                    </form>

                    {{-- TABELA --}}
                    <div class="table-responsive">

                        <table class="table table-hover table-bordered align-middle">

                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>N.º Recibo</th>
                                    <th>Data</th>
                                    <th>Aluno</th>
                                    <th>Código</th>
                                    <th>Valor</th>
                                    <th>Emitido por</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acções</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse($recibos as $recibo)

                                    <tr>
                                        <td>
                                            {{ $recibos->firstItem() + $loop->index }}
                                        </td>

                                        <td>
                                            <strong>{{ $recibo->numero }}</strong>
                                        </td>

                                        <td>
                                            {{ \Carbon\Carbon::parse($recibo->data_emissao)->format('d/m/Y') }}
                                        </td>

                                        <td>
                                            {{ $recibo->aluno_nome ?? '—' }}
                                        </td>

                                        <td>
                                            {{ $recibo->aluno_codigo }}
                                        </td>

                                        <td class="text-end">
                                            <strong>
                                                {{ number_format($recibo->valor, 2, ',', '.') }} MT
                                            </strong>
                                        </td>

                                        <td>
                                            {{ $recibo->emissor_nome }}
                                        </td>

                                        <td>
                                            @if($recibo->estado === 'EMITIDO')
                                                <span class="badge bg-success">
                                                    Emitido
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    Anulado
                                                </span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            @if($recibo->estado === 'EMITIDO')
                                                <a href="{{ route('progresso.recibos.imprimir', ['id' => $recibo->id]) }}"
                                                   class="btn btn-outline-primary btn-sm"
                                                   target="_blank"
                                                   rel="noopener"
                                                   title="Visualizar e imprimir recibo">

                                                    <i class="fa fa-print"></i>
                                                    Imprimir
                                                </a>
                                            @else
                                                <span class="text-muted">
                                                    Indisponível
                                                </span>
                                            @endif
                                        </td>
                                    </tr>

                                @empty
                                    <tr>
                                        <td colspan="9"
                                            class="text-center text-muted py-4">
                                            Nenhum recibo encontrado.
                                        </td>
                                    </tr>
                                @endforelse

                            </tbody>
                        </table>

                    </div>

                    {{-- PAGINAÇÃO --}}
                    <div class="d-flex justify-content-between align-items-center mt-3">

                        <small class="text-muted">
                            Total: {{ $recibos->total() }} recibos
                        </small>

                        <div>
                            {{ $recibos->links() }}
                        </div>

                    </div>

                </div>
            </div>

        </div>
    </div>

</div>

@endsection
