@extends('layouts.app')

@push('css')
@endpush

@section('conteudo')

  <div class="container-fluid">
    <div class="row">
      <div class="col-xl-12">
        <div class="card height-equal title-line">
          <div class="card-header">
            <h2>Registo da Taxa</h2>
            <p class="f-m-light mt-1">
                Faça o Registo da Taxa
            </p>
          </div>
          <div class="card-body basic-wizard important-validation">
            <div id="msform1">
              <form class="row g-3 needs-validation custom-input" id="form">

                @csrf
                
                <form1 class="stepper-one row g-3 needs-validation custom-input" >

                  <div class="col-12">
                    <label class="form-label" for="passwordwizard">Ciclo<span class="txt-danger">*</span></label>
                      <select class="form-select"  name="ciclo">
                          <option value="">Escolhe o Ciclo</option>
                          @foreach($ciclos as $ciclo)
                            <option value="{{$ciclo->id}}">{{$ciclo->nome}} ({{$ciclo->classes}}) </option>
                          @endforeach
                      </select>
                  </div>

                  <div class="col-6">
                    <label class="form-label" for="passwordwizard">Matricula Normal<span class="txt-danger">*</span></label>
                    <input class="form-control"  type="text" name="matricula_normal" placeholder="Matricula Normal">
                  </div>

                  <div class="col-6">
                    <label class="form-label" for="passwordwizard">Matricula Gold<span class="txt-danger">*</span></label>
                    <input class="form-control"  type="text" name="matricula_gold" placeholder="Matricula Gold">
                  </div>

                  <div class="col-6">
                    <label class="form-label" for="passwordwizard">Propina Norma Meio Dia <span class="txt-danger">*</span></label>
                    <input class="form-control"  type="text" name="propina_normal_meio_dia" placeholder="Propina Norma Meio Dia">
                  </div>
                  <div class="col-6">
                    <label class="form-label" for="passwordwizard">Propina Norma Todo Dia <span class="txt-danger">*</span></label>
                    <input class="form-control"  type="text" name="propina_normal_todo_dia" placeholder="Propina Norma Todo Dia">
                  </div>
                  <div class="col-6">
                    <label class="form-label" for="passwordwizard">Propina Gold Meio Dia <span class="txt-danger">*</span></label>
                    <input class="form-control"  type="text" name="propina_gold_meio_dia" placeholder="Propina Gold Meio Dia">
                  </div>
                  <div class="col-6">
                    <label class="form-label" for="passwordwizard">Propina Gold Todo Dia <span class="txt-danger">*</span></label>
                    <input class="form-control"  type="text" name="propina_gold_todo_dia" placeholder="Propina Gold Todo Dia">
                  </div>

                </form1>
                <div class="wizard-footer d-flex gap-2 justify-content-end">
                    <button id="botao_salvar" type="submit" class="btn btn-success w-100">
                        <span id="botao_texto">{{__('Registar a Taxa')}}</span>
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
            ciclo: {
                required: true
            },
            matricula_normal: {
                required: true
            },
            matricula_gold: {
                required: true
            },
            propina_normal_meio_dia: {
                required: true
            },
            propina_normal_todo_dia: {
                required: true
            },
            propina_gold_meio_dia: {
                required: true
            },
            propina_gold_todo_dia: {
                required: true
            }
        },
        submitHandler: function(form) {
            $.ajax({
                type: "POST",
                url: "{{route('taxa.store')}}",
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

