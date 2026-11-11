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
                    <img class="img-40 img-fluid m-r-20" src="{{ URL('/public/assets/images/job-search/2.jpg') }}" alt="">
                    Propina dos Alunos
                </h2>
                <div class="card-header-right-icon">
                    <form action="{{route('relatorio.propinas')}}" method="GET" target="_blank" class="d-flex align-items-center gap-2">
                      <select name="mes" class="form-select" required>
                        <option value="">Seleciona o Mês</option>
                        @foreach($meses as $mes)
                            <option value="{{ $mes->id }}">{{ $mes->nome }}</option>
                        @endforeach
                      </select>

                      <button type="submit" class="btn btn-primary">
                        Imprimir  
                      </button>
                    </form>

                    {{--
                        <a href="{{route('relatorio.propinas')}}" target="_blank" >
                            <button class="btn btn-pill btn-secondary btn-sm">Imprimir</button>
                        </a>
                        
                    --}}



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
                            <th>Nome</th>
                            <th>Genero</th>
                            <th>Classe</th>
                            <th>Ciclo</th>
                            <th>Ult. Propina</th>
                            <th>Estado</th>
                            <th>Acção</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach($alunos as $aluno)

                          
                            <tr>
                                <td></td>
                                <td>{{$aluno->codigo}}</td>
                                <td>{{$aluno->pessoa->nome}}</td>
                                <td>{{$aluno->pessoa->genero}}</td>
                                <td>{{$aluno->classe->nome}}</td>
                                <td>{{$aluno->classe->ciclo->nome}}</td>
                                <td>
                                   @php
                                        // Buscando a última mensalidade paga
                                        $ultimaMensalidade = $aluno->mensalidades()
                                            ->where('pagou', 1)
                                            ->orderBy('mes_id', 'desc')
                                            ->first();
                                    @endphp

                                    {{ optional($ultimaMensalidade->mes ?? null)->nome ?? 'N/A' }}-{{ optional($ultimaMensalidade->anoAcademico ?? null)->ano ?? 'N/A' }}
    
                                </td>

                                <td>
                                    @if($aluno->suspenso==1)
                                        <a class="btn btn-danger btn-xs" href="#!">Suspenso</a>
                                    @else
                                        <a class="btn btn-success btn-xs" href="#!">Activo</a>
                                    @endif
                                </td>
                                <td>
                                    <a class="btn btn-primary btn-xs activate-btn" href="{{route('propina.show',['alunoID'=>$aluno->codigo])}}" >
                                        Detalhes
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
