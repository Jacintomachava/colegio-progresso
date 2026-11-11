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
                    <h2>Editar Curricula</h2>
                    <p class="f-m-light mt-1">
                        Faça o edit de Curriculo
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

                    <input class="form-control"  type="hidden" name="curriculoID" value="{{$curriculo->id}}"  >

                    <div class="col-6">
                        <label class="form-label" for="classe">Classe</label>
                        <select class="form-select" name="classe" id="classe">
                            @foreach ($classes as $classe)
                                <option value="{{ $classe->id }}" 
                                    {{ $classe->id == $curriculo->classe_id ? 'selected' : '' }}>
                                    {{ $classe->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                

                    <div class="col-6">
                        <label class="form-label" for="estado">Tem Exame</label>
                        <select class="form-select" name="tem_exame" id="tem_exame">
                            <option value="1" {{ $curriculo->tem_exame == 1 ? 'selected' : '' }}>SIM</option>
                            <option value="0" {{ $curriculo->tem_exame == 0 ? 'selected' : '' }}>NÃO</option>
                        </select>
                    </div>



                    <div class="col-6">
                        <label class="form-label" for="estado">Estado</label>
                        <select class="form-select" name="estado" id="estado">
                            <option value="1" {{ $curriculo->estado == 1 ? 'selected' : '' }}>Ativo</option>
                            <option value="0" {{ $curriculo->estado == 0 ? 'selected' : '' }}>Desativo</option>
                        </select>
                    </div>

                    <div class="col-6">
                        <label class="form-label" for="estado">Descrição</label>
                        <input class="form-control"  type="text" name="descricao" value="{{$curriculo->descricao}}" placeholder="Ex: Curriculo">
                    </div>
  
                  </form1>
                  <div class="wizard-footer d-flex gap-2 justify-content-end">
                      <button id="botao_salvar" type="submit" class="btn btn-success w-100">
                          <span id="botao_texto">{{__('Editar Curriculo')}} </span>
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
                url: "{{route('curriculoClasse.update')}}",
                data: $(form).serialize(), // Corrigido para usar `form` em vez de `this`

                beforeSend: function () {
                    // Desabilita o botão de envio e altera o ícone para mostrar que está autenticando
                    $('#botao_salvar').attr('disabled', true);
                    $('#icon_enviar').removeClass('ri-arrow-right-line').addClass('spinner-border ri-loader-2-line');
                    $('#botao_texto').text('Editar Curriculo...');
                },

                success: function(response) {
                    // Habilita o botão e retorna o ícone original
                    $('#botao_salvar').attr('disabled', false);
                    $('#icon_enviar').removeClass('spinner-border ri-loader-2-line').addClass('ri-arrow-right-line');
                    $('#botao_texto').text('Editar Curriculo');

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
                    $('#botao_texto').text('Editar Curriculo');

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

