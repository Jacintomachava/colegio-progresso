@extends('layouts.app')

@push('css')

@endpush

@section('conteudo')



    @if(Auth::user()->codigo == '02.P006.25')

            <br><br>
            <div class="col-xl-12 col-md-12 box-col-12">
                <div class="file-content">
                  <div class="card">
                    <div class="card-header">
                      <div class="d-flex">

                      </div>
                    </div>
                    <div class="card-body file-manager">  

                      <ul class="files">
                       @foreach($ciclosarquivados as $ciclosarquivado)
                          <a href="{{route('arquivo.pautas',$ciclosarquivado->ano)}}">  
                            <li class="file-box">
                            <div class="file-top">  <i class="fa fa-file-archive-o txt-secondary"></i><i class="fa fa-ellipsis-v f-14 ellips"></i></div>
                            <div class="file-bottom">
                                <h6>Ano Lectivo {{$ciclosarquivado->ano}}</h6>
                                <p class="mb-1">Pauta.pdf</p>
                            </div>
                            </li>
                         </a>  
                       @endforeach

                      </ul>

                    </div>
                  </div>
                </div>
              </div>

    @endif



@endsection

@push('js')


@endpush
