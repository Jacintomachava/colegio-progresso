@extends('layouts.app')

@push('css')

@endpush

@section('conteudo')

<div class="row">

    <div class="col-xl-6 box-col-6">
        <div class="card title-line upgrade-card overflow-hidden">
            <div class="row align-items-end"> 
            <div class="col-sm-8 col-11"> 
                <div class="card-header"> 
                <h2> {{ Auth::user()->nome }}  </h2>
                <p class="mt-2 f-light">Esta página vai te ajudar na gestão pedagógica. </p>
                <a class="btn btn-primary btn-hover-effect btn-sm f-w-500" href="{{route('minhasTurmas.notas')}}">Minhas Turmas
                    <svg class="svg-sprite">
                    <use href="{{ URL('/assets/svg/icon-sprite.svg#logout')}}"> </use>
                    </svg></a>
                </div>
            </div>
            </div>
            <div class="cartoon-image"> <img class="img-fluid" src="{{ URL('/assets/images/dashboard/1.png')}}" alt="vector"></div><img class="img-fluid pattern-image" src="{{ URL('/assets/images/dashboard/bg-1.png')}}" alt="vector pattern">
        </div>
    </div>

    <div class="col-xl-6 box-col-6">
        <div class="row tread-cards">
            <div class="col-xl-6 col-sm-6"> 
            <div class="card widget-1">
                <div class="card-body common-box">
                <div class="widget-icon primary widget-round">
                    <svg>
                    <use href="{{ URL('/assets/svg/icon-sprite.svg#crown')}}"> </use>
                    </svg>
                </div>
                <div> 
                    <h3 class="f-w-600">Turmas</h3><span class="f-w-500 f-light f-12">{{count($turmaProfessor)}} </span>
                </div>
                </div>
            </div>
            </div>
            <div class="col-xl-6 col-sm-6"> 
            <div class="card widget-1">
                <div class="card-body common-box">
                <div class="widget-icon success widget-round">
                    <svg>
                    <use href="{{ URL('/assets/svg/icon-sprite.svg#flash')}}"> </use>
                    </svg>
                </div>
                <div> 
                    <h3 class="f-w-600">Disciplinas</h3><span class="f-w-500 f-light f-12">{{count($turmaProfessorDisciplinas)}}</span>
                </div>
                </div>
            </div>
            </div>
            <div class="col-xl-6 col-sm-6"> 
            <div class="card widget-1"> 
                <div class="card-body common-box">
                <div class="widget-icon warning widget-round">
                    <svg>
                    <use href="{{ URL('/assets/svg/icon-sprite.svg#blend-2')}}"> </use>
                    </svg>
                </div>
                <div> 
                    <h3 class="f-w-600">Alunos</h3><span class="f-w-500 f-light f-12">{{count($alunosMatriculados)}}</span>
                </div>
                </div>
            </div>
            </div>
            <div class="col-xl-6 col-sm-6"> 
            <div class="card widget-1">
                <div class="card-body common-box">
                <div class="widget-icon secondary widget-round">
                    <svg>
                    <use href="{{ URL('/assets/svg/icon-sprite.svg#color-filter')}}"> </use>
                    </svg>
                </div>
                <div> 
                    <h3 class="f-w-600">Pauta Fi.</h3><span class="f-w-500 f-light f-12">{{count($pautaFinalizada)}}</span>
                </div>
                </div>
            </div>
            </div>
        </div>
    </div>

    @if(Auth::user()->codigo == '02.P006.25')

        <div class="row">
            <div class="col-xl-4 col-md-4 col-sm-4">
              <a href="{{route('aluno.indexPautas')}}">
                <div class="card project-widget widget-1 title-line">
                    <div class="card-header card-no-border pb-0"> 
                    <div class="header-top">
                        <div> 
                        <h3 class="f-w-600">Gerir Alunos</h3>
                        </div>
                        <div class="card-header-right-icon">
                        <div class="dropdown icon-dropdown d-xxl-none1">
                            <button class="btn dropdown-toggle" id="active-project" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <svg>
                                <use href="{{ URL('/public/assets/svg/icon-sprite.svg#more-horizontal') }}"></use>
                            </svg>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="active-project">
                            </div>
                        </div>
                        </div>
                    </div>
                    <div class="widget-middle-content">
                        <div class="d-flex align-items-center"> 
                        <h2>{{$totalAlunosMatriculado}}</h2>
                            <span class="f-w-500 txt-warning f-12"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trending-up me-1"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg><span>Ciclo Activo {{$anoAtivo->ano}}</span></span>
                        </div>
                    </div>
                    </div>
                    <div class="card-body widget-bottom-content">
                    <div class="progress" style="height: 5px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 56%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    </div>
                </div>
             </a>
            </div>

            <div class="col-xl-4 col-md-4 col-sm-4">
               <a href="{{route('curriculo.index')}}">  
                <div class="card project-widget widget-1 title-line">
                    <div class="card-header card-no-border pb-0"> 
                    <div class="header-top">
                        <div> 
                        <h3 class="f-w-600">Gerir Curriculo</h3>
                        </div>
                        <div class="card-header-right-icon">
                        <div class="dropdown icon-dropdown d-xxl-none1">
                            <button class="btn dropdown-toggle" id="active-project" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <svg>
                                <use href="{{ URL('/public/assets/svg/icon-sprite.svg#more-horizontal') }}"></use>
                            </svg>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="active-project">
                            </div>
                        </div>
                        </div>
                    </div>
                    <div class="widget-middle-content">
                        <div class="d-flex align-items-center"> 
                        <h2>{{$totalCurriculos}}</h2>
                            <span class="f-w-500 txt-warning f-12"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trending-up me-1"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg><span>Ano 2026</span></span>
                        </div>
                    </div>
                    </div>
                    <div class="card-body widget-bottom-content">
                    <div class="progress" style="height: 5px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 56%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    </div>
                </div>
              </a> 
            </div>

            <div class="col-xl-4 col-md-4 col-sm-4">
              <a href="{{route('meu.arquivo')}}"> 
                <div class="card project-widget widget-1 title-line">
                    <div class="card-header card-no-border pb-0"> 
                    <div class="header-top">
                        <div> 
                        <h3 class="f-w-600">Arquivo Pauta</h3>
                        </div>
                        <div class="card-header-right-icon">
                        <div class="dropdown icon-dropdown d-xxl-none1">
                            <button class="btn dropdown-toggle" id="active-project" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <svg>
                                <use href="{{ URL('/public/assets/svg/icon-sprite.svg#more-horizontal') }}"></use>
                            </svg>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="active-project">
                            </div>
                        </div>
                        </div>
                    </div>
                    <div class="widget-middle-content">
                        <div class="d-flex align-items-center"> 
                        <h2>{{$totalAnoAcademico}}</h2>
                            <span class="f-w-500 txt-warning f-12"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trending-up me-1"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg><span>Ano 2026</span></span>
                        </div>
                    </div>
                    </div>
                    <div class="card-body widget-bottom-content">
                    <div class="progress" style="height: 5px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 56%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    </div>
                </div>
              </a> 
            </div>

        </div>

    @endif



    <div class="col-12 col-xl-100 order-xl-i">
        <div class="offer-banner">
        <div class="offer-content"> 
            <h2>
                @if(count($turmaProfessor) > 0)
                    {{ number_format((count($pautaFinalizada) / count($turmaProfessor)) * 100, 2) }}% Pautas por finalizar.
                @else
                    0% Pautas Por Finalizada
                @endif
            </h2>
            <p class="f-w-500 f-12">Estimado professor finalize a pauta da sua turma.</p>
        </div>
            <div class="cartoon-image"></div>
        </div>
    </div>

</div>

@endsection

@push('js')


@endpush
