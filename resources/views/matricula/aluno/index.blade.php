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
                    Matriculas
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
                            <th></th>
                            <th>Codigo</th>
                            <th>Classe</th>
                            <th>Turma</th>
                            <th>Periodo</th>
                            <th>Ano A.</th>
                            <th>Pago</th>
                            <th>Data</th>
                            <th>Acção</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach($matriculas as $matricula)
                            <tr>
                                <td></td>
                                <td>{{$matricula->codigo}}</td>
                                <td>{{$matricula->classe->nome}}</td>
                                <td>@if($matricula->turma_id!=null) {{$matricula->turma->nome}} @else ... @endif </td>
                                <td>@if($matricula->periodo_id!=null) {{$matricula->periodo->nome}} @else ... @endif </td>
                                <td>{{$matricula->anoAcademico->ano}}</td>
                                <td>
                                    @if($matricula->pagou==1)
                                        <a class="btn btn-success btn-xs" href="#!">SIM</a>
                                    @else
                                        <a class="btn btn-danger btn-xs" href="#!">NÃO</a>
                                    @endif
                                </td>    

                                <td>{{$matricula->created_at->format('d-M-Y')}}</td>
                                <td>
                                        <a class="btn btn-warning btn-xs activate-btn" target="_blank" href="{{route('matricula.facturaPDF',['factura'=>$matricula->pagamento->referencia])}}">
                                            Factura
                                        </a>
                                    @if($matricula->pagou==1)
                                        <a class="btn btn-success btn-xs activate-btn" target="_blank" href="{{route('matricula.reciboPDF',['factura'=>$matricula->pagamento->referencia])}}">
                                            Recibo
                                        </a>
                                    @endif
                                        <a class="btn btn-primary btn-xs activate-btn" target="_blank" href="{{route('propina.factura',['alunoCodigo'=>$matricula->aluno->codigo])}}" >
                                            Propinas
                                        </a>


                                    {{-- 
                                        @if($matricula->pagou==0)
                                            <a href="{{route('pagamentoMatriculaMpesa.index',['codigo'=>$matricula->codigo])}}" class="btn btn-danger btn-xs" href="#!">Pagar Por Mpesa</a> 
                                        @endif
                                    --}}



                                </td>
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
