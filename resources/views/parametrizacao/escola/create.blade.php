@extends('layouts.app')

@push('css')
@endpush

@section('conteudo')

  <div class="container-fluid">
    <div class="row">
      <div class="col-xl-12">
        <div class="card height-equal title-line">
          <div class="card-header">
            <h2>Registo da Escola</h2>
            <p class="f-m-light mt-1">
                Registo da Escola
            </p>
          </div>
          <div class="card-body basic-wizard important-validation">
            <div id="msform1">
              <form class="row g-3 needs-validation custom-input" id="form" enctype="multipart/form-data">

                @csrf
                
                <form1 class="stepper-one row g-3 needs-validation custom-input" >

                  <div class="col-3">
                    <label class="form-label" for="passwordwizard">Nome da Escola<span class="txt-danger">*</span></label>
                    <input class="form-control"  type="text" name="nome" placeholder="Nome da Escola">
                  </div>

                  <div class="col-3">
                    <label class="form-label" for="passwordwizard">Logotipo<span class="txt-danger">*</span></label>
                    <input class="form-control"  type="file" name="imagem" placeholder="Imagem">
                  </div>

                  <div class="col-3">
                    <label class="form-label" for="passwordwizard">Tipo<span class="txt-danger">*</span></label>
                      <select class="form-select"  name="tipo">
                          <option value="">Escolhe o Tipo</option>
                          <option value="Sucursal">Sucursal</option>
                          <option value="Sede">Sede</option>
                      </select>
                  </div>

                  <div class="col-3">
                    <label class="form-label" for="passwordwizard">Telefone 1<span class="txt-danger">*</span></label>
                    <input class="form-control"  type="text" name="telefone1" placeholder="Telefone 1">
                  </div>

                  <div class="col-3">
                    <label class="form-label" for="passwordwizard">Telefone 2<span class="txt-danger">*</span></label>
                    <input class="form-control"  type="text" name="telefone2" placeholder="Telefone 2">
                  </div>

                  <div class="col-3">
                    <label class="form-label" for="passwordwizard">Endereco<span class="txt-danger">*</span></label>
                    <input class="form-control"  type="text" name="endereco" placeholder="Endereco">
                  </div>

                  <div class="col-3">
                    <label class="form-label" for="passwordwizard">Codigo<span class="txt-danger">*</span></label>
                    <input class="form-control"  type="text" name="codigo" placeholder="Codigo">
                  </div>

                  <div class="col-3">
                    <label class="form-label" for="passwordwizard">Slogan<span class="txt-danger">*</span></label>
                    <input class="form-control"  type="text" name="slogan" placeholder="Slogan">
                  </div>


                </form1>
                <div class="wizard-footer d-flex gap-2 justify-content-end">
                    <button id="botao_salvar" type="submit" class="btn btn-success w-100">
                        <span id="botao_texto">{{__('Registar Escola')}}</span>
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
            // Use FormData para suportar envio de arquivos
            var formData = new FormData(form);

            $.ajax({
                type: "POST",
                url: "{{ route('escola.store') }}",
                data: formData,
                processData: false, // Não processa os dados, necessário para FormData
                contentType: false, // Define o content type como multipart/form-data
                beforeSend: function() {
                    $('#botao_salvar').attr('disabled', true);
                    $('#icon_enviar').removeClass('ri-arrow-right-line').addClass('spinner-border ri-loader-2-line');
                    $('#botao_texto').text('Registando Funcionario...');
                },
                success: function(response) {
                    $('#botao_salvar').attr('disabled', false);
                    $('#icon_enviar').removeClass('spinner-border ri-loader-2-line').addClass('ri-arrow-right-line');
                    $('#botao_texto').text('Entrar');

                    if (response.status == 1) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: response.message,
                        });
                        window.location.reload();
                    } else if (response.status == 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro!',
                            text: response.message,
                        });
                    }
                },
                error: function(errors) {
                    $('#botao_salvar').attr('disabled', false);
                    $('#icon_enviar').removeClass('spinner-border ri-loader-2-line').addClass('ri-arrow-right-line');
                    $('#botao_texto').text('Entrar');
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

