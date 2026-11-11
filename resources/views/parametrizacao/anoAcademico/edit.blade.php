@extends('layouts.app')

@push('css')
@endpush

@section('conteudo')

  <div class="container-fluid">
    <div class="row">
      <div class="col-xl-12">
        <div class="card height-equal title-line">
          <div class="card-header">
            <h2>Editar o Ano Acadêmico</h2>
            <p class="f-m-light mt-1">
                Faça a edição do Ano Acadêmico
            </p>
          </div>
          <div class="card-body basic-wizard important-validation">
            <div id="msform1">
              <form class="row g-3 needs-validation custom-input" id="form">

                @csrf
                
                <form1 class="stepper-one row g-3 needs-validation custom-input" >

                  <input class="form-control"  type="hidden" name="id" value="{{$anoAcademico->id}}" placeholder="ID">

                  <div class="col-4">
                    <label class="form-label" for="passwordwizard">Ano<span class="txt-danger">*</span></label>
                      <select class="form-select"  name="ano">
                          <option value="">Escolhe o ano</option>
                           @for ($year = now()->year; $year >= now()->year - 3; $year--)
                              <option value="{{ $year+1 }}" @if($anoAcademico->ano == $year+1) selected @endif>{{ $year+1 }}</option>
                           @endfor
                      </select>
                  </div>

                  <div class="col-4">
                    <label class="form-label" for="passwordwizard">Nome Ano Lectivo<span class="txt-danger">*</span></label>
                    <input class="form-control"  type="text" name="nome" value="{{$anoAcademico->nome}}" placeholder="Nome Ano Lectivo">
                  </div>

                  <div class="col-4">
                    <label class="form-label" for="passwordwizard">Data Fim Matricula<span class="txt-danger">*</span></label>
                    <input class="form-control"  type="date" value="{{$anoAcademico->data_fim_matricula}}" name="data_fim_matricula" placeholder="Matricula">
                  </div>

                  <div class="col-4">
                    <label class="form-label" for="passwordwizard">Data Inicio Renovação<span class="txt-danger">*</span></label>
                    <input class="form-control"  type="date" value="{{$anoAcademico->data_inicio_renovacao}}" name="data_inicio_renovacao" placeholder="Renovação">
                  </div>

                  <div class="col-4">
                    <label class="form-label" for="passwordwizard">Data Fim Renovação<span class="txt-danger">*</span></label>
                    <input class="form-control"  type="date" value="{{$anoAcademico->data_fim_renovacao}}" name="data_fim_renovacao" placeholder="Renovação">
                  </div>

                  <div class="col-4">
                    <label class="form-label" for="passwordwizard">Data Fim Lancamento Notas<span class="txt-danger">*</span></label>
                    <input class="form-control"  type="date" value="{{$anoAcademico->data_fim_lancamento_pauta}}" name="data_fim_lancamento_pauta" placeholder="Renovação">
                  </div>

                  <div class="col-4">
                    <label class="form-label" for="passwordwizard">Trimestre Activo<span class="txt-danger">*</span></label>
                    <select class="form-select"  name="trimestre">
                          <option value="1" @if($anoAcademico->trimestre == 1) selected @endif> 1° Trimestre</option>
                          <option value="2" @if($anoAcademico->trimestre == 2) selected @endif> 2° Trimestre</option>
                          <option value="3" @if($anoAcademico->trimestre == 3) selected @endif> 3° Trimestre</option>
                    </select>
                  </div>

                  <div class="col-4">
                    <label class="form-label" for="passwordwizard">Resultado Final<span class="txt-danger">*</span></label>
                    <select class="form-select"  name="resultado">
                          <option value="0" @if($anoAcademico->pauta_finalizada == 0) selected @endif> Pendente</option>
                          <option value="1" @if($anoAcademico->pauta_finalizada == 1) selected @endif> Divulgar</option>
                    </select>
                  </div>

                  <div class="col-4">
                    <label class="form-label" for="passwordwizard">Notificacao Por SMS<span class="txt-danger">*</span></label>
                    <select class="form-select"  name="sms">
                          <option value="0" @if($anoAcademico->sms == 0) selected @endif> Desativo</option>
                          <option value="1" @if($anoAcademico->sms == 1) selected @endif> Activo</option>
                    </select>
                  </div>

                </form1>
                <div class="wizard-footer d-flex gap-2 justify-content-end">
                    <button id="botao_salvar" type="submit" class="btn btn-success w-100">
                        <span id="botao_texto">{{__('Actualizacao do ano acadêmico')}}</span>
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
                minlength: 2
            },
            ano: {
                required: true,
            },
            descricao: {
                required: true,
                minlength: 2
            }
        },
        submitHandler: function(form) {
            $.ajax({
                type: "POST",
                url: "{{route('academico.update')}}",
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

