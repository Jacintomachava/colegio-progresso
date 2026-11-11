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
                    <h2>Pagamento da Propina {{$propina->anoAcademico->ano}}</h2>
                    <p class="f-m-light mt-1">
                        Faça o pagamento da Propina de <b><u><i>{{$propina->mes->nome}}  {{\Carbon\Carbon::parse($propina->data_inicio)->format('Y')}}</i></u></b>
                    </p>
                </div> 
                <div class="col-5">
                    <br>
                    <h2>Total a Pagar: <span id="totalPagar">{{number_format(($propina->valor+$propina->valor_co_curricular)-$propina->desconto+($propina->multa/100*($propina->valor+$propina->valor_co_curricular)), 2, ',', '.') }}</span></h2>
                </div> 
            </div>          
          </div>

          @if($propina->pagou==0)

            <div class="card-body basic-wizard important-validation">
              <div id="msform1">

              <form class="row g-3 needs-validation custom-input" id="msform" enctype="multipart/form-data">

                  @csrf
                  
                  <form1 class="stepper-one row g-3 needs-validation custom-input" >

                    <input class="form-control"  type="hidden" name="propinaID" value="{{$propina->id}}"  >

                    <div class="col-3">
                      <label class="form-label" for="passwordwizard">Nome do Aluno<span class="txt-danger">*</span></label>
                      <input class="form-control"  type="text" name="nome" value="{{$propina->aluno->pessoa->nome}}" readonly style="background-color: #f5f5f5; color: #555;">
                    </div>

                    <div class="col-3">
                      <label class="form-label" for="passwordwizard">Codigo Aluno<span class="txt-danger">*</span></label>
                      <input class="form-control"  type="text" name="aluno" value="{{$propina->aluno->codigo}}" readonly style="background-color: #f5f5f5; color: #555;">
                    </div>

                    <div class="col-3">
                      <label class="form-label" for="passwordwizard">Data Pagamento<span class="txt-danger">*</span></label>
                      <input class="form-control"  type="date" name="data_pagamento" id="data_pagamento" value="{{ now()->toDateString() }}" readonly style="background-color: #f5f5f5; color: #555;">
                    </div>

                    <div class="col-3">
                      <label class="form-label" for="passwordwizard">Tipo Pagamento</label>
                      <select class="form-select"  name="tipo_pagamento">
                        <option value="2">Normal</option>
                        <option value="1">Com Disconto</option>
                        <option value="0">Com Isenção</option>
                      </select>
                  </div>



                    <div class="col-3">
                      <label class="form-label" for="passwordwizard">Valor Aula Normal<span class="txt-danger">*</span></label>
                      <input class="form-control"  type="text" name="valor" id="valor" value="{{$propina->valor}}" readonly style="background-color: #f5f5f5; color: #555;">
                    </div>

                    <div class="col-3">
                      <label class="form-label" for="passwordwizard">Valor CoCurricular<span class="txt-danger">*</span></label>
                      <input class="form-control"  type="text" name="valor_co_curricular" id="valorCoCurricular" value="{{$propina->valor_co_curricular}}" readonly style="background-color: #f5f5f5; color: #555;">
                    </div>

                    <div class="col-3">
                      <label class="form-label" for="passwordwizard">Multa (%)<span class="txt-danger">*</span></label>
                      <input class="form-control"  type="text" name="multa" id="multa" value="{{$propina->multa}}%" >
                    </div>

                    <div class="col-3" id="referencia">
                      <label class="form-label" for="passwordwizard">Codigo de Talao de Deposito<span class="txt-danger">*</span></label>
                      <input class="form-control"  type="text" name="referencia"  >
                    </div>

                    <div class="col-12">
                      <label class="form-label" for="passwordwizard">Desconto<span class="txt-danger">*</span></label>
                      <input class="form-control"  type="text" name="desconto" id="valorDesconto" value="{{$propina->desconto}}" >
                    </div>

                    <div class="col-12">
                      <label class="form-label" for="passwordwizard">Motivo<span class="txt-danger">*</span></label>
                      <textarea class="form-control" name="motivo" id="motivo" rows="3"></textarea>
                    </div>

                  </form1>
                  <div class="wizard-footer d-flex gap-2 justify-content-end">
                      <button id="botao_salvar" type="submit" class="btn btn-success w-100">
                          <span id="botao_texto">{{__('Pagar Propina de ')}} {{$propina->mes->nome}}</span>
                          <i id="icon_enviar" class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
                      </button>
                  </div>
              </form> 
  
            </div>
              <br>

            </div>

          @else
              <br><br>
                <center>
                  <h1>Mensalidade <b><u><i>{{$propina->mes->nome}}  {{\Carbon\Carbon::parse($propina->data_inicio)->format('Y')}}</i></u></b> Paga com Sucesso</h1>
                  <br><br>
                  <a href="{{route('propina.index')}}" class="btn btn-danger btn-sm" >Pagar Outras Mensalidade</a> <a target="_blank" href="{{route('propinaRecibo.recibo',['referencia'=>$propina->pagamento->referencia])}}" class="btn btn-success btn-sm" >Imprimir Recibo</a> 
                </center>
              <br><br>
          @endif

        </div>
      </div>
    </div>
  </div>

@endsection


@push('js')

<script>
  let valorOriginal = 0;
  let coCurricularOriginal = 0;

  function atualizarCampos() {
    const tipoPagamento = document.querySelector('[name="tipo_pagamento"]').value;
    const motivo = document.getElementById('motivo');
    const descontoField = document.getElementById('valorDesconto');
    const valorInput = document.getElementById('valor');
    const coCurricularInput = document.getElementById('valorCoCurricular');
    const campoReferencia = document.getElementById('referencia');

    if (tipoPagamento === '2') { // Normal
      motivo.closest('.col-12').style.display = 'none';
      descontoField.closest('.col-12').style.display = 'none';
      campoReferencia.style.display = "block";

      // Restaurar os valores originais
      valorInput.value = valorOriginal;
      coCurricularInput.value = coCurricularOriginal;

    } else if (tipoPagamento === '1') { // Com Desconto
      motivo.closest('.col-12').style.display = 'block';
      descontoField.closest('.col-12').style.display = 'block';
      campoReferencia.style.display = "block";

      // Restaurar os valores originais
      valorInput.value = valorOriginal;
      coCurricularInput.value = coCurricularOriginal;

    } else if (tipoPagamento === '0') { // Isenção
      motivo.closest('.col-12').style.display = 'block';
      descontoField.closest('.col-12').style.display = 'none';
      campoReferencia.style.display = "none";

      // Zerar os valores base
      valorInput.value = '0';
      coCurricularInput.value = '0';
    }

    calcularTotal();
  }

  function calcularTotal() {
    const tipoPagamento = document.querySelector('[name="tipo_pagamento"]').value;

    const valor = parseFloat(document.getElementById('valor').value) || 0;
    const coCurricular = parseFloat(document.getElementById('valorCoCurricular').value) || 0;
    const multaTexto = document.getElementById('multa').value.replace('%', '');
    const multa = parseFloat(multaTexto) || 0;
    const desconto = parseFloat(document.getElementById('valorDesconto').value) || 0;

    let total = valor + coCurricular;

    if (tipoPagamento === '0') {
      total = 0;
    } else {
      total += (total * multa) / 100;
      if (tipoPagamento === '1') {
        total -= desconto;
      }
    }

    document.getElementById('totalPagar').innerText = total.toFixed(2);
    document.getElementById('valorTotalHidden').value = total.toFixed(2);
  }

  document.addEventListener('DOMContentLoaded', function () {
    // Guardar os valores originais ao carregar a página
    valorOriginal = document.getElementById('valor').value;
    coCurricularOriginal = document.getElementById('valorCoCurricular').value;

    const campos = ['valor', 'valorCoCurricular', 'multa', 'valorDesconto', 'tipo_pagamento'];
    campos.forEach(id => {
      const el = document.getElementById(id) || document.querySelector(`[name="${id}"]`);
      if (el) {
        el.addEventListener('input', () => {
          if (id === 'tipo_pagamento') {
            atualizarCampos();
          } else {
            calcularTotal();
          }
        });
      }
    });

    atualizarCampos(); // Inicial
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
                url: "{{route('pagarPropina.store')}}",
                data: $(form).serialize(), // Corrigido para usar `form` em vez de `this`

                beforeSend: function () {
                    // Desabilita o botão de envio e altera o ícone para mostrar que está autenticando
                    $('#botao_salvar').attr('disabled', true);
                    $('#icon_enviar').removeClass('ri-arrow-right-line').addClass('spinner-border ri-loader-2-line');
                    $('#botao_texto').text('Pagar Propina...');
                },

                success: function(response) {
                    // Habilita o botão e retorna o ícone original
                    $('#botao_salvar').attr('disabled', false);
                    $('#icon_enviar').removeClass('spinner-border ri-loader-2-line').addClass('ri-arrow-right-line');
                    $('#botao_texto').text('Pagar Propina');

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
                    $('#botao_texto').text('Pagar Propina');

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

