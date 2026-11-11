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
            <div class="row">
                <div class="col-7">
                    <h2>Pagamento da Matricula {{$anoAcademicoAtivo->ano+1}}</h2>
                    <p class="f-m-light mt-1">
                        Faça o pagamento da matricula Via Mpesa
                    </p>
                </div> 
                <div class="col-5">
                    <br>
                    <h2>Total a Pagar: <span id="totalPagar">{{$matricula->valor_total - $matricula->desconto}}</span></h2>
                </div> 
            </div>          
          </div>
          <div class="card-body basic-wizard important-validation">
            <div id="msform1">

            @if($matricula->pagou==0)

            <form class="row g-3 needs-validation custom-input" id="msform" enctype="multipart/form-data">

                @csrf
                
                <form1 class="stepper-one row g-3 needs-validation custom-input" >

                  <input class="form-control"  type="hidden" name="codigo" value="{{$matricula->codigo}}"  >

                  <div class="col-6">
                    <label class="form-label" for="passwordwizard">Nome do Aluno<span class="txt-danger">*</span></label>
                    <input class="form-control"  type="text" name="nome" value="{{$matricula->aluno->pessoa->nome}}" readonly style="background-color: #f5f5f5; color: #555;">
                  </div>

                  <div class="col-6">
                    <label class="form-label" for="passwordwizard">Codigo Aluno<span class="txt-danger">*</span></label>
                    <input class="form-control"  type="text" name="aluno" value="{{$matricula->aluno->codigo}}" readonly style="background-color: #f5f5f5; color: #555;">
                  </div>


                  <div class="col-6">
                    <label class="form-label" for="passwordwizard">Valor A Pagar<span class="txt-danger">*</span></label>
                    <input class="form-control"  type="text" name="valor_total" id="valorTotal" value="{{$matricula->valor_total}}" readonly  style="background-color: #f5f5f5; color: #555;" >
                  </div>

                  <div class="col-6" id="referencia">
                    <label class="form-label" for="passwordwizard">Telefone com Mpesa<span class="txt-danger">*</span></label>
                    <input class="form-control"  type="text" name="telefone"  >
                  </div>


                </form1>
                <div class="wizard-footer d-flex gap-2 justify-content-end">
                    <button id="botao_salvar" type="submit" class="btn btn-success w-100">
                        <span id="botao_texto">{{__('Pagar Matricula')}}</span>
                        <i id="icon_enviar" class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
                    </button>
                </div>
            </form> 

            @else
                <br><br>
                <center>
                  <h1>Matricula Paga com Sucesso</h1>
                  <br><br>
                  <a href="{{route('matricula.index')}}" class="btn btn-danger btn-sm" >Pagar Outra Matricula</a> <a target="_blank" href="{{route('matricula.reciboPDF',['factura'=>$matricula->pagamento->referencia])}}" class="btn btn-success btn-sm" >Imprimir Recibo</a> 
                </center>
              <br><br>
            @endif
 
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

    $("#msform").validate({
        // Adicionar regras para cada campo
        rules: {
            telefone: {
                required: true,
                minlength: 9,
                minlength: 9,
            }
        },
        submitHandler: function(form) {
            $.ajax({
                type: "POST",
                url: "{{route('pagarMatriculaMpesa.store')}}",
                data: $(form).serialize(), // Corrigido para usar `form` em vez de `this`

                beforeSend: function () {
                    // Desabilita o botão de envio e altera o ícone para mostrar que está autenticando
                    $('#botao_salvar').attr('disabled', true);
                    $('#icon_enviar').removeClass('ri-arrow-right-line').addClass('spinner-border ri-loader-2-line');
                    $('#botao_texto').text('Pagar Matricula...');
                },

                success: function(response) {
                    // Habilita o botão e retorna o ícone original
                    $('#botao_salvar').attr('disabled', false);
                    $('#icon_enviar').removeClass('spinner-border ri-loader-2-line').addClass('ri-arrow-right-line');
                    $('#botao_texto').text('Pagar Matricula');

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
                    $('#botao_texto').text('Pagar Matricula');

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

