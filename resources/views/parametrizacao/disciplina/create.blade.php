@extends('layouts.app')

@push('css')
<style>
  .hidden {
      display: none;
      opacity: 0;
      transition: opacity 0.3s ease;
  }

  .visible {
      display: block;
      opacity: 1;
      transition: opacity 0.3s ease;
  }
</style>  
@endpush

@section('conteudo')

  <div class="container-fluid">
    <div class="row">
      <div class="col-xl-12">
        <div class="card height-equal title-line">
          <div class="card-header">
            <h2>Registo da Disciplina</h2>
            <p class="f-m-light mt-1">
                Faça o Registo da Disciplina
            </p>
          </div>
          <div class="card-body basic-wizard important-validation">
            <div id="msform1">
              <form class="row g-3 needs-validation custom-input" id="form">

                @csrf
                
                <form1 class="stepper-one row g-3 needs-validation custom-input" >

                  <div class="col-6">
                    <label class="form-label" for="passwordwizard">Nome da Disciplina<span class="txt-danger">*</span></label>
                    <input class="form-control"  type="text" name="nome" placeholder="Nome da Disciplina">
                  </div>

                  <div class="col-6">
                    <label class="form-label" for="passwordwizard">Tipo Disciplina<span class="txt-danger">*</span></label>
                      <select class="form-select" id="tipoDisciplina" name="tipo">
                          <option value="">Escolhe o Tipo da Disciplina</option>
                          <option value="0">Curricular</option>
                          <option value="1">Co-Curricular</option>
                      </select>
                  </div>

                  <div class="row">
                    <div class="col-6">
                      <label class="form-label" for="passwordwizard">Descrição da Disciplina<span class="txt-danger">*</span></label>
                      <input class="form-control"  type="text" name="descricao" placeholder="Descrição da Disciplina">
                    </div>

                      <div class="col-6" id="coCurricularDiv" style="display: none;">
                        <label class="form-label" for="passwordwizard">Valor Propinas<span class="txt-danger">*</span></label>
                        <input class="form-control"  type="text" name="propina" placeholder="Valor Propinas">
                      </div>
                  </div>


                </form1>
                <div class="wizard-footer d-flex gap-2 justify-content-end">
                    <button id="botao_salvar" type="submit" class="btn btn-success w-100">
                        <span id="botao_texto">{{__('Registar Disciplina')}}</span>
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
            tipo: {
                required: true,
            }
        },
        submitHandler: function(form) {
            $.ajax({
                type: "POST",
                url: "{{route('disciplina.store')}}",
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

<script>
    // Obtém o elemento select e a div
    const tipoDisciplina = document.getElementById('tipoDisciplina');
    const coCurricularDiv = document.getElementById('coCurricularDiv');

    // Adiciona um evento para escutar mudanças no select
    tipoDisciplina.addEventListener('change', function () {
        if (this.value === '1') { // Valor para Co-Curricular
            coCurricularDiv.style.display = 'block';
        } else {
            coCurricularDiv.style.display = 'none';
        }
    }); 

    /*tipoDisciplina.addEventListener('change', function () {
        if (this.value === '1') {
            coCurricularDiv.classList.remove('hidden');
            coCurricularDiv.classList.add('visible');
        } else {
            coCurricularDiv.classList.remove('visible');
            coCurricularDiv.classList.add('hidden');
        }
    }); */

</script>


@endpush

