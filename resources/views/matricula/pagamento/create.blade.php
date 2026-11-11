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
                    <h2>Pagamento da Matricula {{$anoAcademicoAtivo->ano}}</h2>
                    <p class="f-m-light mt-1">
                        Faça o pagamento da matricula
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

                  <input class="form-control"  type="hidden" name="codigo" value="{{$matricula->id}}"  >

                  <div class="col-4">
                    <label class="form-label" for="passwordwizard">Nome do Aluno<span class="txt-danger">*</span></label>
                    <input class="form-control"  type="text" name="nome" value="{{$matricula->aluno->pessoa->nome}}" readonly style="background-color: #f5f5f5; color: #555;">
                  </div>

                  <div class="col-4">
                    <label class="form-label" for="passwordwizard">Codigo Aluno<span class="txt-danger">*</span></label>
                    <input class="form-control"  type="text" name="aluno" value="{{$matricula->aluno->codigo}}" readonly style="background-color: #f5f5f5; color: #555;">
                  </div>

                  <div class="col-4">
                      <label class="form-label" for="passwordwizard">Tipo Pagamento</label>
                      <select class="form-select"  name="tipo_pagamento">
                        <option value="2">Normal</option>
                        <option value="1">Com Disconto</option>
                        <option value="0">Com Isenção</option>
                      </select>
                  </div>

                  <div class="col-4">
                    <label class="form-label" for="passwordwizard">Valor A Pagar<span class="txt-danger">*</span></label>
                    <input class="form-control"  type="text" name="valor_total" id="valorTotal" value="{{$matricula->valor_total}}" readonly  style="background-color: #f5f5f5; color: #555;" >
                  </div>

                  <div class="col-4">
                    <label class="form-label" for="passwordwizard">Data Pagamento<span class="txt-danger">*</span></label>
                    <input class="form-control"  type="date" name="data_pagamento" id="data_pagamento" value="{{ now()->toDateString() }}" readonly style="background-color: #f5f5f5; color: #555;">
                  </div>


                  <div class="col-4" id="referencia">
                    <label class="form-label" for="passwordwizard">Codigo de Talao de Deposito<span class="txt-danger">*</span></label>
                    <input class="form-control"  type="text" name="referencia"  >
                  </div>

                  <div class="col-12">
                    <label class="form-label" for="passwordwizard">Valor Desconto<span class="txt-danger">*</span></label>
                    <input class="form-control"  type="text" name="desconto" id="valorDesconto" value="{{$matricula->desconto}}" >
                  </div>
                  <div class="col-12">
                    <label class="form-label" for="passwordwizard">Motivo<span class="txt-danger">*</span></label>
                    <textarea class="form-control" name="motivo" id="motivo" rows="3"></textarea>
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

document.addEventListener('DOMContentLoaded', function () {
  const valorTotalInput = document.getElementById('valorTotal');
  const valorDescontoInput = document.getElementById('valorDesconto');
  const totalPagarSpan = document.getElementById('totalPagar');

  function atualizarTotalPagar() {
    const valorTotal = parseFloat(valorTotalInput.value) || 0;
    const valorDesconto = parseFloat(valorDescontoInput.value) || 0;
    const totalPagar = Math.max(valorTotal - valorDesconto, 0);
    totalPagarSpan.textContent = totalPagar.toFixed(2);
  }

  valorDescontoInput.addEventListener('input', atualizarTotalPagar);
});

</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const tipoPagamento = document.querySelector('select[name="tipo_pagamento"]');
    const campoDesconto = document.getElementById('valorDesconto').closest('.col-12');
    const campoMotivo = document.getElementById('motivo').closest('.col-12');
    const valorTotal = document.getElementById('valorTotal');
    const totalPagarSpan = document.getElementById('totalPagar');
    const campoReferencia = document.getElementById('referencia');

    const valorOriginal = "{{$matricula->valor_total}}"; // valor original do Laravel
    
    
    function atualizarCampos() {
        const tipo = tipoPagamento.value;

        if (tipo === "2") {
            // Normal
            campoDesconto.style.display = "none";
            campoMotivo.style.display = "none";
            campoReferencia.style.display = "block";
            valorTotal.value = valorOriginal;
            totalPagarSpan.textContent = valorOriginal;
        } else if (tipo === "1") {
            // Com Desconto
            campoDesconto.style.display = "block";
            campoMotivo.style.display = "block";
            campoReferencia.style.display = "block";
            valorTotal.value = valorOriginal;
            totalPagarSpan.textContent = valorOriginal;
        } else if (tipo === "0") {
            // Com Isenção
            campoDesconto.style.display = "none";
            campoMotivo.style.display = "block";
            campoReferencia.style.display = "none";
            valorTotal.value = "0";
            total = 0.00;
            totalPagarSpan.textContent = total;
        }
    }

    tipoPagamento.addEventListener("change", atualizarCampos);
    atualizarCampos(); // aplica no carregamento inicial
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
            desconto: {
                required: true,
                min: 0
            },
            data_pagamento:{
              required: true
            }
        },
        submitHandler: function(form) {
            $.ajax({
                type: "POST",
                url: "{{route('pagarMatricula.store')}}",
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

