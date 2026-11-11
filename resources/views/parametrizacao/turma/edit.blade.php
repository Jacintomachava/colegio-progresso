@extends('layouts.app')

@push('css')
@endpush

@section('conteudo')

  <div class="container-fluid">
    <div class="row">
      <div class="col-xl-12">
        <div class="card height-equal title-line">
          <div class="card-header">
            <h2>Editar de Turma</h2>
            <p class="f-m-light mt-1">
                Editar Turmas de {{$anoAcademico->nome}}
            </p>
          </div>
          <div class="card-body basic-wizard important-validation">
            <div id="msform1">
              <form class="row g-3 needs-validation custom-input" id="form">

                @csrf

                <input class="form-control"  type="hidden" name="turma"  value="{{$turma->id}}" >
                
                <form1 class="stepper-one row g-3 needs-validation custom-input" >

                  <div class="col-8">
                    <label class="form-label" for="passwordwizard">Classe<span class="txt-danger">*</span></label>
                      <select class="form-select"  name="classe">
                          <option value="">Escolhe a Classe</option>
                           @foreach($classes as $classe)
                                <option value="{{$classe->id }}"  @if($classe->id==$turma->classe_id) selected @endif >{{ $classe->nome }} - {{ $classe->ciclo->nome }} </option>
                           @endforeach
                      </select>
                  </div>

                  <div class="col-4">
                    <label class="form-label" for="passwordwizard">Periodo <span class="txt-danger">*</span></label>
                      <select class="form-select"  name="periodo">
                          <option value="">Escolhe o Periodo</option>
                           @foreach($periodos as $periodo)
                                <option value="{{$periodo->id }}"  @if($periodo->id==$turma->periodo_id) selected @endif>{{ $periodo->detalhe }} ({{ $periodo->descricao }}) </option>
                           @endforeach
                      </select>
                  </div>

                  <div class="col-4">
                    <label class="form-label" for="passwordwizard">Nome da Turma<span class="txt-danger">*</span></label>
                    <input class="form-control"  type="text" name="nome"  value="{{$turma->nome}}" placeholder="Nome da Turma">
                  </div>

                  <div class="col-4" hidden>
                    <label class="form-label" for="passwordwizard">Ano Academico Activo<span class="txt-danger">*</span></label>
                      <select class="form-select"  name="ano">
                          <option value="{{$anoAcademico->id}}">{{$anoAcademico->nome}}</option>
                      </select>
                  </div>

                  <div class="col-4">
                    <label class="form-label" for="passwordwizard">Numero de Vaga<span class="txt-danger">*</span></label>
                    <input class="form-control"  type="text" name="vaga" value="{{$turma->vaga}}" placeholder="Numero de vaga">
                  </div>

                  <div class="col-4">
                    <label class="form-label" for="passwordwizard">Escola <span class="txt-danger">*</span></label>
                      <select class="form-select"  name="escola">
                          <option value="">Escolhe a Escola</option>
                           @foreach($escolas as $escola)
                                <option value="{{$escola->id }}" @if($escola->id==Auth::user()->escola_id) selected @endif >{{ $escola->nome }} </option>
                           @endforeach
                      </select>
                  </div>

                </form1>
                <div class="wizard-footer d-flex gap-2 justify-content-end">
                    <button id="botao_salvar" type="submit" class="btn btn-success w-100">
                        <span id="botao_texto">{{__('Criar Turma')}}</span>
                        <i id="icon_enviar" class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
                    </button>
                </div>
                </form>  
              </div>
              <br>

          </div>
        </div>
      </div>
    </div>
  </div>


@endsection

@push('js')

<script>

$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("#form").validate({
        // Adicionar regras para cada campo
        rules: {
            nome: {
                required: true,
                minlength: 1
            },
            vaga: {
                required: true,
            }
        },
        submitHandler: function(form) {
            $.ajax({
                type: "POST",
                url: "{{route('turma.update')}}",
                data: $(form).serialize(), // Corrigido para usar `form` em vez de `this`

                beforeSend: function () {
                    // Desabilita o botão de envio e altera o ícone para mostrar que está autenticando
                    $('#botao_salvar').attr('disabled', true);
                    $('#icon_enviar').removeClass('ri-arrow-right-line').addClass('spinner-border ri-loader-2-line');
                    $('#botao_texto').text('Autenticando...');
                },

                success: function(response) {
                    // Habilita o botão e retorna o ícone original
                    $('#botao_salvar').attr('disabled', false);
                    $('#icon_enviar').removeClass('spinner-border ri-loader-2-line').addClass('ri-arrow-right-line');
                    $('#botao_texto').text('Entrar');

                    // Redireciona ou exibe uma mensagem de erro com base na resposta
                    if(response.status == 1) {

                      Swal.fire({
                                icon: 'success',
                                title: 'Sucesso!',
                                text: response.message,
                      });

                      window.location.reload();

                    } else if(response.status == 0) {

                      Swal.fire({
                            icon: 'error',
                            title: 'Erro!',
                            text: response.message,
                      });

                    }
                },
                error: function(errors) {
                    // Habilita o botão e retorna o ícone original em caso de erro
                    $('#botao_salvar').attr('disabled', false);
                    $('#icon_enviar').removeClass('spinner-border ri-loader-2-line').addClass('ri-arrow-right-line');
                    $('#botao_texto').text('Entrar');

                    // Exibe a mensagem de erro
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: errors.responseJSON.message,
                    });

                }
            });
        }
    });
});

</script>


@endpush

