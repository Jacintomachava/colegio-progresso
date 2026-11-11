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
                    Disciplinas
                </h2>
                <div class="card-header-right-icon">
                    <a href="{{route('disciplina.create')}}">
                        <button class="btn btn-pill btn-primary btn-sm">Adicionar Disciplina</button>
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
                            <th></th>
                            <th>Nome</th>
                            <th>Descrição</th>
                            <th>Tipo</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach($disciplinas as $disciplina)
                            <tr>
                                <td></td>
                                <td>{{$disciplina->nome}}</td>
                                <td title="{{ $disciplina->descricao }}">{{ \Illuminate\Support\Str::limit($disciplina->descricao, 30, '...') }}</td>
                                <td>
                                    @if($disciplina->co_curricular == 0)
                                        Curricular
                                    @else
                                        Co-Curricular
                                    @endif
                                </td>
                                <td>{{$disciplina->created_at->format('d-M-Y')}}</td>
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
