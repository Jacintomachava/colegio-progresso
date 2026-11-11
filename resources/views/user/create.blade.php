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
            <h2>Actualizar utilizador</h2>
            <p class="f-m-light mt-1">
                Actualizar utilizador:  {{$user->codigo}}
            </p>
          </div>
          <div class="card-body basic-wizard important-validation">
            <div id="msform1">
              <form class="row g-3 needs-validation custom-input" id="form" enctype="multipart/form-data">

                @csrf
                
                <form1 class="stepper-one row g-3 needs-validation custom-input" >


                     <div class="col-sm-6">
                        <label class="form-label" for="email-basic-wizard">Nome de Funcionario<span class="txt-danger">*</span></label>
                        <input class="form-control"  type="text" name="nome" placeholder="Nome de Funcionario"  value="{{$user->nome}}">
                      </div>

                      <div class="col-sm-6" hidden>
                        <label class="form-label" for="firstnamewizard">Utilizador<span class="txt-danger">*</span></label>
                        <input class="form-control"  type="text" name="utilizador" placeholder="Utilizador" value="{{$user->codigo}}">
                      </div>

                      <div class="col-6">
                        <label class="form-label" for="passwordwizard">Estado<span class="txt-danger">*</span></label>
                        <select class="form-select" name="estado">
                            <option value="1" {{ $user->estado == 1 ? 'selected' : '' }}>Ativo</option>
                            <option value="0" {{ $user->estado == 0 ? 'selected' : '' }}>Desativo</option>
                        </select>
                    </div>

                    <div class="col-6">
                        <label class="form-label" for="passwordwizard">Escola<span class="txt-danger">*</span></label>
                        <select class="form-select" name="escola_id">
                            <option value="">Escolha a Escola...</option>
                            @if($user->todas_escolas==1)
                                <option value="0" selected>Todas Escolas</option>
                                @foreach($escolas as $escola)
                                  <option value="{{$escola->id}}" >{{$escola->nome}} - {{$escola->codigo}}</option>
                                @endforeach
                            @else
                                @foreach($escolas as $escola)
                                  <option value="{{$escola->id}}" @if($user->escola_id==$escola->id) selected @endif>{{$escola->nome}} - {{$escola->codigo}}</option>
                                @endforeach
                                  <option value="0" >Todas Escolas</option>
                            @endif
                        </select>
                    </div>

                    <div class="form-group col-6">
                        <label for="roles" class="form-label">Perfil<span class="txt-danger">*</span></label>
                        <select class="form-select contactos" name="roles[]" multiple="multiple">
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" {{ in_array($role->id, $userRoles) ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>



                </form1>
                <div class="wizard-footer d-flex gap-2 justify-content-end">
                    <button id="botao_salvar" type="submit" class="btn btn-success w-100">
                        <span id="botao_texto">{{__('Actualizar Utilizador')}}</span>
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
            placeholder: "{{ __('Seleciona o(s) Perfil') }}",
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
                url: "{{ route('user.update') }}",
                data: formData,
                processData: false, // Não processa os dados, necessário para FormData
                contentType: false, // Define o content type como multipart/form-data
                beforeSend: function() {
                    $('#botao_salvar').attr('disabled', true);
                    $('#icon_enviar').removeClass('ri-arrow-right-line').addClass('spinner-border ri-loader-2-line');
                    $('#botao_texto').text('Actualizar Utilizador...');
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

