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
                    Taxas
                </h2>
                <div class="card-header-right-icon">
                    <a href="{{route('taxa.create')}}">
                        <button class="btn btn-pill btn-primary btn-sm">Adicionar Taxas Curricular</button>
                    </a>
                    <a href="{{route('disciplina.create')}}">
                        <button class="btn btn-pill btn-warning btn-sm">Adicionar Taxas Co-Curricular</button>
                    </a>
                </div>
            </div>
        </div>
    </div> <!-- Fechamento da div "card title-line overflow-hidden member-wrapper" -->
</div> <!-- Fechamento da div "col-xl-12 order-md-iii" -->



<div class="col-sm-12" style="margin-top: -2%"> 
    <div class="card title-line">
        <div class="card-block row">
        <div class="col-sm-12 col-lg-12 col-xl-12">
            <div class="table-responsive custom-scrollbar">
                <table class="table">
                    <tbody>

                        <tr class="table-dark">
                            <th colspan="8"><center>Curricular</center></th>
                        </tr>
                        <tr class="table-dark">
                            <th scope="col">Ciclos</th>
                            <th scope="col">Classes</th>
                            <th scope="col">Matricula Normal</th>
                            <th scope="col">Matricula Gold</th>
                            <th scope="col">P. Normal (Meio Dia)</th>
                            <th scope="col">P. Normal (Todo Dia)</th>
                            <th scope="col">P. Gold (Meio Dia)</th>
                            <th scope="col">P. Gold (Todo Dia)</th>
                        </tr>
                        @foreach($taxas as $taxa)
                            <tr>
                                <td>{{$taxa->ciclo->nome}}</td>
                                <td>{{$taxa->ciclo->classes}}</td>
                                <td>{{number_format($taxa->matricula_normal, 2, ',', '.')}} </td>
                                <td>{{number_format($taxa->matricula_gold, 2, ',', '.')}} </td>
                                <td>{{number_format($taxa->propina_meio_dia_normal, 2, ',', '.')}} </td>
                                <td>{{number_format($taxa->propina_todo_dia_normal, 2, ',', '.')}} </td>
                                <td>{{number_format($taxa->propina_meio_dia_gold, 2, ',', '.')}} </td>
                                <td>{{number_format($taxa->propina_todo_dia_gold, 2, ',', '.')}} </td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
                <table class="table">
                    <tbody>

                        <tr class="table-dark">
                            <th colspan="8"><center>Co-Curricular</center></th>
                        </tr>
                        <tr class="table-dark">
                            <th scope="col">Ciclos</th>
                            <th scope="col">Aulas</th>
                            <th scope="col">Descrição</th>
                            <th scope="col">Valor Propina</th>
                        </tr>
                        @foreach($disciplinas as $disciplina)
                            <tr>
                                <td >Todos Ciclos</td>
                                <td>{{$disciplina->nome}}</td>
                                <td>Propinas</td>
                                <td>{{number_format($disciplina->propina, 2, ',', '.')}} </td>
                            </tr>
                        @endforeach
     
                    </tbody>
                </table>
            </div>
        </div>
        </div>
    </div>
</div>


@endsection

@push('js')



@endpush
