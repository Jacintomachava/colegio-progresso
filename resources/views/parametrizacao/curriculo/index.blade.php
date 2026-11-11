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
                    Curriculo Classe
                </h2>
                <div class="card-header-right-icon">
                    <a class="btn btn-info btn-xs activate-btn" href="#">
                        Cadastrar Curriculo
                    </a>
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
                            <th>#</th>
                            <th>Nome</th>
                            <th>Tem Exame</th>
                            <th>Disciplinas</th>
                            <th>Data</th>
                            <th>Editar</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach($curriculos as $curriculo)
                            <tr>
                                <td>{{$curriculo->id}}</td>
                                <td>{{$curriculo->classe->nome}}</td>
                                <td>
                                     @if($curriculo->tem_exame)
                                     <span style="color: green">SIM</span>
                                    @else
                                     <span style="color: red">NÃO</span>
                                    @endif
                                </td>
                                <td>
                                  <a class="btn btn-info btn-xs activate-btn" href="{{route('curriculo.show',['classeID'=>$curriculo->classe->id])}}">
                                      Disciplinas
                                  </a>
                                </td>
                                <td>{{$curriculo->created_at->format('d-M-Y')}}</td>
                                <td>
                                  <a class="btn btn-warning btn-xs activate-btn" href="{{route('curriculoClasse.edit',['classeID'=>$curriculo->classe->id])}}">
                                      Editar
                                  </a>
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
