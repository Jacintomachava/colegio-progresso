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
                <p class="mt-2 f-light">Esse Sistema de gestao Vao ti ajudar no controlo de propinas notas e matriculas</p>
                    <a class="btn btn-primary btn-hover-effect btn-sm f-w-500" href="{{route('minhasTurmas.index')}}">Minhas Turmas
                        <svg class="svg-sprite">
                        <use href="{{ URL('/assets/svg/icon-sprite.svg#logout')}}"> </use>
                        </svg>
                    </a>
                </div>
            </div>
            </div>
            <div class="cartoon-image"> <img class="img-fluid" src="{{ URL('/assets/images/dashboard/1.png')}}" alt="vector"></div><img class="img-fluid pattern-image" src="{{ URL('/assets/images/dashboard/bg-1.png')}}" alt="vector pattern">
        </div>
    </div>

    <div class="col-xl-3 col-md-4 col-sm-6">
        <div class="card project-widget widget-1 title-line">
            <div class="card-header card-no-border pb-0"> 
            <div class="header-top">
                <div> 
                <h3 class="f-w-600">A frequentar</h3>
                </div>
                <div class="card-header-right-icon">
                <div class="dropdown icon-dropdown d-xxl-none1">
                    <button class="btn dropdown-toggle" id="active-project" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <svg>
                        <use href="{{ URL('/assets/svg/icon-sprite.svg#more-horizontal')}}"></use>
                    </svg>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="active-project">
                    </div>
                </div>
                </div>
            </div>
            <br>
            <div class="widget-middle-content">
                <div class="d-flex align-items-center"> 
                  <h2>{{$matricula->classe->nome}}</h2>
                </div>
            </div>
            </div>
            <div class="card-body widget-bottom-content">
                <div class="progress" style="height: 5px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 56%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <br>
                <h3>Ano Lectivo {{$matricula->anoAcademico->ano}}</h3>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-4 col-sm-6">
        <div class="card project-widget widget-1 title-line">
            <div class="card-header card-no-border pb-0"> 
            <div class="header-top">
                <div> 
                <h3 class="f-w-600">Mensalidades</h3>
                </div>
                <div class="card-header-right-icon">
                <div class="dropdown icon-dropdown d-xxl-none1">
                    <button class="btn dropdown-toggle" id="active-project" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <svg>
                        <use href="{{ URL('/assets/svg/icon-sprite.svg#more-horizontal')}}"></use>
                    </svg>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="active-project">
                    </div>
                </div>
                </div>
            </div>
            <br>
            <div class="widget-middle-content">
                <div class="d-flex align-items-center"> 
                  <h2>
                    {{count($mensalidades)}} em Atraso
                  </h2>
                </div>
            </div>
            </div>
            <div class="card-body widget-bottom-content">
                <div class="progress" style="height: 5px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 56%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>

                </div>
                <br>
                <h3>Ano Lectivo {{$matricula->anoAcademico->ano}}</h3>
            </div>
        </div>
    </div>
    

    <div class="col-12 col-xl-100 order-xl-i">
        <div class="offer-banner">
            <div class="offer-content"> 
                <h2>Informações</h2>
                <p class="f-w-500 f-12">
                    Mantenha as suas propinas em dia  
                    evite multas e a supensao, pode pagar as 
                    suas propinas a qualquer momento respeitando
                    os prazos.
                </p>
            </div>
        </div>
    </div>

</div>

@endsection

@push('js')


@endpush
