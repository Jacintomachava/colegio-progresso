@extends('layouts.app')

@push('css')

@endpush

@section('conteudo')

  <div class="container-fluid">
    <div class="row">
      <div class="col-xl-12">
        <div class="card height-equal title-line">
          <div class="card-header">
            <div class="row">
                <div class="col-7">
                    <h2>Enviar Mensagem </h2>
                    <p class="f-m-light mt-1">
                        Faça o envio de SMS  
                    </p>
                </div> 
                <div class="col-5">

                </div> 
            </div>          
          </div>


            <div class="card-body basic-wizard important-validation">
              <div id="msform1">

              <form class="row g-3 needs-validation custom-input" id="msform" enctype="multipart/form-data">

                  @csrf
                  
                  <form1 class="stepper-one row g-3 needs-validation custom-input" >


                    <div class="col-7">
                      <label class="form-label" for="passwordwizard">Destinatarios<span class="txt-danger">*</span></label>
                        <select class="form-select"  name="destinatario">
                              <option value="">Seleciona o Destinatarios</option>
                              <option value="alunos">Alunos/Encarregado</option>
                              <option value="professores">Professores</option>
                        </select>
                    </div>
                    <div class="col-2"></div>
                    <div class="col-3"><h1>Resumo:</h1></div>

                    <div class="col-7" hidden>
                      <label class="form-label" for="passwordwizard">Seleciona a Escola<span class="txt-danger">*</span></label>
                        <select class="form-select"  name="escola">
                          <option value="">Todas Escolas</option>
                          @foreach($escolas as $escola)
                              <option value="{{$escola->id}}">{{$escola->nome}}</option>
                          @endforeach
                        </select>
                    </div>
                    <div class="col-2" hidden></div>
                    <div class="col-3" hidden><h1>Resumo:</h1></div>

                    <div class="col-7">
                        <label class="form-label" for="mensagem">Mensagem SMS<span class="txt-danger">*</span></label>
                        <textarea class="form-control" id="mensagem" name="mensagem" rows="4" placeholder="Digite a mensagem aqui..."></textarea>
                    </div>
                    <div class="col-2"></div>
                    <div class="col-3">
                      <div class="mt-2" id="smsResumo">
                          
                          Encoding: <span id="encoding">GSM_7BIT</span><br>
                          Caracteres: <span id="caracteres">0</span><br>
                          <h3>Mensagens: <span id="mensagens">0</span><br></h3>
                          Por mensagem: <span id="porMensagem">160</span><br>
                          Remanescente: <span id="remanescente">160</span>
                      </div>
                    </div>

                    

                    <div class="col-7">
                      <label class="form-label" for="passwordwizard">Data Envio<span class="txt-danger">*</span></label>
                      <input class="form-control"  type="date" name="data_envio" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}"  >
                    </div>
                    <div class="col-2"></div>
                    <div class="col-3"></div>

                  </form1>
                  <div class="col-7">
                    <div class="wizard-footer d-flex gap-2 justify-content-end">
                        <button id="botao_salvar" type="submit" class="btn btn-success w-100">
                            <span id="botao_texto">{{__('Enviar SMS')}} </span>
                            <i id="icon_enviar" class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
                        </button>
                    </div>
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
const textarea = document.getElementById("mensagem");
const caracteresEl = document.getElementById("caracteres");
const mensagensEl = document.getElementById("mensagens");
const remanescenteEl = document.getElementById("remanescente");
const porMensagemEl = document.getElementById("porMensagem");
const encodingEl = document.getElementById("encoding");

// Função para contar caracteres e calcular SMS
function atualizarResumo() {
    const text = textarea.value;
    const length = text.length;

    // Aqui assumimos GSM 7-bit simples (160 por mensagem)
    const porMensagem = 160;
    const mensagens = Math.ceil(length / porMensagem);
    const remanescente = (mensagens * porMensagem) - length;

    caracteresEl.textContent = length;
    mensagensEl.textContent = mensagens;
    remanescenteEl.textContent = remanescente;
    porMensagemEl.textContent = porMensagem;
    encodingEl.textContent = "GSM_7BIT"; // Aqui poderia detectar Unicode se quiser
}

// Atualiza em tempo real
textarea.addEventListener("input", atualizarResumo);
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
            mensagem: {
                required: true,
                 minlength: 2,
            },
            destinatario: {
                required: true
            }
     },
        submitHandler: function(form) {
            $.ajax({
                type: "POST",
                url: "{{route('mensagem.storeSMS')}}",
                data: $(form).serialize(), // Corrigido para usar `form` em vez de `this`

                beforeSend: function () {
                    // Desabilita o botão de envio e altera o ícone para mostrar que está autenticando
                    $('#botao_salvar').attr('disabled', true);
                    $('#icon_enviar').removeClass('ri-arrow-right-line').addClass('spinner-border ri-loader-2-line');
                    $('#botao_texto').text('Regsitando Furo...');
                },

                success: function(response) {
                    // Habilita o botão e retorna o ícone original
                    $('#botao_salvar').attr('disabled', false);
                    $('#icon_enviar').removeClass('spinner-border ri-loader-2-line').addClass('ri-arrow-right-line');
                    $('#botao_texto').text('Registar Furo');

                    // Redireciona ou exibe uma mensagem de erro com base na resposta
                    if(response.status == 1) {

                         Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: response.message,
                            confirmButtonText: 'OK'
                        }).then((result) => {

                            if (result.isConfirmed) {
                              
                                window.location.reload();
                            }

                        });
                          
                    }

                    if(response.status == 0) {

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
                    $('#botao_texto').text('Registar Furo');

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

