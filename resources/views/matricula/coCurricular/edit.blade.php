@extends('layouts.app')

@push('css')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

@endpush

@section('conteudo')

  <div class="container-fluid">
    <div class="row">
      <div class="col-xl-12">
        <div class="card height-equal title-line">
          <div class="card-header">
            <h2>Aulas Co-Curricular Ano Academico {{$anoAcademicoAtivo->ano}}</h2>
            <p class="f-m-light mt-1">
                Faça a edição das AUlas Co-Curricular
            </p>
          </div>
          <div class="card-body basic-wizard important-validation">
            <div id="msform1">
            <form class="row g-3 needs-validation custom-input" id="msform" enctype="multipart/form-data">

                @csrf
                
                <form1 class="stepper-one row g-3 needs-validation custom-input" >

                <input class="form-control"  type="hidden" name="aluno" value="{{$aluno->id}}" >

                  <div class="col-6">
                    <label class="form-label" for="passwordwizard">Nome do Aluno<span class="txt-danger">*</span></label>
                    <input class="form-control"  type="text" name="nome" value="{{$aluno->pessoa->nome}}" disabled>
                  </div>

                  <div class="col-6">
                    <label class="form-label" for="passwordwizard">Codigo Aluno<span class="txt-danger">*</span></label>
                    <input class="form-control"  type="text" name="codigo" value="{{$aluno->codigo}}" disabled>
                  </div>

                  <div class="form-group col-6">
                      <label for="contactos" class="form-label">{{ __('Disciplina Co-Curriculares') }} <span class="txt-danger">*</span></label>
                      <select class="form-select contactos1" name="cocurriculares[]"  multiple="multiple" required>
                          @foreach ($disciplinas as $disciplina)
                              <option value="{{ $disciplina->id }}" {{ in_array($disciplina->id, $coCurriculares) ? 'selected' : '' }}>
                                    {{ $disciplina->nome }}
                              </option>
                          @endforeach
                      </select>
                  </div>

                  <div class="col-6">
                      <label class="form-label" for="passwordwizard">Mes<span class="txt-danger">*</span></label>
                        <select class="form-select"  name="mes">
                          <option value="">Escolhe o Mes...</option>
                          @foreach($meses as $mes)
                            <option value="{{$mes->id}}">{{$mes->nome}}</option>
                          @endforeach
                        </select>
                  </div>

                </form1>
                <div class="wizard-footer d-flex gap-2 justify-content-end">
                    <button id="botao_salvar" type="submit" class="btn btn-success w-100">
                        <span id="botao_texto">{{__('Actualizacao Aulas Co-Curricular')}}</span>
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

<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('.contactos').select2({
            placeholder: "{{ __('Seleciona Alunos Irmaos') }}",
            allowClear: true
        });
    });
    $(document).ready(function() {
        $('.contactos1').select2({
            placeholder: "{{ __('Seleciona Actividades Co-Curricular') }}",
            allowClear: true
        });
    });

    $(document).ready(function() {
        $('.contactos2').select2({
            placeholder: "{{ __('Seleciona Relacao Pessoa com Aluno') }}",
            allowClear: true
        });
    });

</script>



<script>

$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("#msform").validate({
        // Adicionar regras para cada campo
        rules: {
            nome: {
                required: true,
                minlength: 2
            },
            email: {
                email: true,
            },
            sigla: {
                required: true,
                minlength: 2
            },
            endereco: {
                required: true,
                minlength: 2
            },
            telefone: {
                required: true,
                number: true,
                minlength: 9,
                maxlength: 9
            },
            conta_bancaria: {
                required: true
            },
            logotipo: {
                required: true
            },
            br: {
                required: true
            },
            certidao: {
                required: true
            },
            nome_presidente: {
                required: true
            },
            tipo_documento_presidente: {
                required: true
            },
            numero_documento_presidente: {
                required: true
            },
            telefone_presidente: {
                required: true,
                number: true,
                minlength: 9,
                maxlength: 9
            },
            email_presidente: {
                required: true,
                email: true
            },
            foto_presidente: {
                required: true
            },
            nome_secretario: {
                required: true
            },
            tipo_documento_secretario: {
                required: true
            },
            numero_documento_secretario: {
                required: true
            },
            telefone_secretario: {
                required: true,
                number: true,
                minlength: 9,
                maxlength: 9
            },
            email_secretario: {
                required: true,
                email: true
            },
            foto_secretario: {
                required: true
            }
        },
        submitHandler: function(form) {
            $.ajax({
                type: "POST",
                url: "{{route('cocurricular.update')}}",
                data: $(form).serialize(), // Corrigido para usar `form` em vez de `this`

                beforeSend: function () {
                    // Desabilita o botão de envio e altera o ícone para mostrar que está autenticando
                    $('#botao_salvar').attr('disabled', true);
                    $('#icon_enviar').removeClass('ri-arrow-right-line').addClass('spinner-border ri-loader-2-line');
                    $('#botao_texto').text('Matriculando...');
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
                    $('#botao_texto').text('Matricular');

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

