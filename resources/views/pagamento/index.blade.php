@extends('layouts.app')

@push('css')
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
@endpush

@section('conteudo')

<div class="col-xl-12 order-md-iii">
    <div class="card title-line overflow-hidden member-wrapper">
        <div class="card-header card-no-border">
            <div class="header-top">
                <h2>
                    <img class="img-40 img-fluid m-r-20" src="../assets/images/job-search/2.jpg" alt="">
                    Estado da Referencia
                </h2>
                <div class="card-header-right-icon">
                    ...
                </div>
            </div>
        </div>
    </div> <!-- Fechamento da div "card title-line overflow-hidden member-wrapper" -->
</div> <!-- Fechamento da div "col-xl-12 order-md-iii" -->

<div class="col-sm-12" style="margin-top: -2%">
    <div class="card">
        <div class="card-header pb-0 card-no-border">
        </div>
        <div class="card-body">

            <div class="table-responsive custom-scrollbar">
                <table class="display" id="basic-1">
                    <thead>
                        <tr>
                            <th> </th>
                            <th> Referencia</th>
                            <th> Valor</th>
                            <th> Categoria</th>
                            <th> Aluno</th>
                            <th> Estado</th>
                            <th> Data</th>
                            <th> Hora</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach($pagamentos as $pagamento)
                            <tr>
                                <td> </td>
                                <td> {{$pagamento->referencia}} </td>
                                <td> {{ number_format($pagamento->valor, 2, ',', '.') }} </td>
                                <td> {{$pagamento->categoria->nome}} </td>
                                <td> {{$pagamento->aluno->pessoa->nome}} </td>
                                <td>                                                         
                                    @if($pagamento->pagou == 1)
                                         <a class="btn btn-success btn-xs" href="#!">Refe. Pago</a>
                                    @else
                                       @if($pagamento->cancelado == 1)
                                         <a class="btn btn-danger btn-xs" href="#!">Cancelado</a>
                                       @else
                                         <a class="btn btn-warning btn-xs" href="#!">Pendente</a>
                                       @endif
                                    @endif
                                </td>
                                <td> {{\Carbon\Carbon::parse($pagamento->updated_at)->format('d-M-Y')}} </td>
                                <td> {{ \Carbon\Carbon::parse($pagamento->updated_at)->format('H:i') }} </td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

@endsection

@push('js')



@endpush
