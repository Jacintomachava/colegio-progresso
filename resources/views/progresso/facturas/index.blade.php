@extends('layouts.app')

@section('conteudo')

<div class="container-fluid">
    <div class="card">

        <div class="card-header">
            <h4>Gestão de Facturas</h4>
            <p class="text-muted mb-0">
                Consulta de facturas emitidas aos alunos
            </p>
        </div>

        <div class="card-body">

            <form method="GET"
                  action="{{ route('progresso.facturas.index') }}"
                  class="row g-3 mb-4">

                <div class="col-md-5">
                    <label class="form-label">Pesquisar</label>

                    <input type="text"
                           name="pesquisa"
                           class="form-control"
                           placeholder="Número, nome ou código do aluno"
                           value="{{ request('pesquisa') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Data inicial</label>

                    <input type="date"
                           name="data_inicio"
                           class="form-control"
                           value="{{ request('data_inicio') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Data final</label>

                    <input type="date"
                           name="data_fim"
                           class="form-control"
                           value="{{ request('data_fim') }}">
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button class="btn btn-primary">
                        <i class="fa fa-search"></i>
                        Pesquisar
                    </button>

                    <a href="{{ route('progresso.facturas.index') }}"
                       class="btn btn-outline-secondary">
                        Limpar
                    </a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>N.º Factura</th>
                            <th>Data</th>
                            <th>Aluno</th>
                            <th>Código</th>
                            <th class="text-center">Acções</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($facturas as $factura)
                            <tr>
                                <td>
                                    {{ $facturas->firstItem() + $loop->index }}
                                </td>

                                <td>
                                    <strong>{{ $factura->numero }}</strong>
                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse($factura->created_at)->format('d/m/Y') }}
                                </td>

                                <td>
                                    {{ $factura->aluno_nome ?? '—' }}
                                </td>

                                <td>
                                    {{ $factura->aluno_codigo }}
                                </td>

                                <td class="text-center">
                                    <a href="{{ route('progresso.facturas.imprimir', ['id' => $factura->id]) }}"
                                       class="btn btn-outline-primary btn-sm"
                                       target="_blank"
                                       rel="noopener">
                                        <i class="fa fa-print"></i>
                                        Imprimir
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6"
                                    class="text-center text-muted py-4">
                                    Nenhuma factura encontrada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

            <div class="mt-3">
                {{ $facturas->links() }}
            </div>

        </div>
    </div>
</div>

@endsection