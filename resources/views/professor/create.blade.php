@extends('layouts.app')

@push('css')
@endpush

@section('conteudo')

  <div class="container-fluid">
    <div class="row">
      <div class="col-xl-12">
        <div class="card height-equal title-line">
          <div class="card-header">
            <h2>Registar Professor</h2>
            <p class="f-m-light mt-1">
                Registo do Professor
            </p>
          </div>
          <div class="card-body basic-wizard important-validation">
            <div id="msform1">
              <form class="row g-3 needs-validation custom-input" id="form" enctype="multipart/form-data">

                @csrf
                
                <form1 class="stepper-one row g-3 needs-validation custom-input" >


                    <div class="col-sm-3">
                        <label class="form-label" for="email-basic-wizard">Nome de Professor<span class="txt-danger">*</span></label>
                        <input class="form-control"  type="text" name="nome" placeholder="Nome de Professor">
                      </div>
                      <div class="col-sm-3">
                        <label class="form-label" for="firstnamewizard">Data de Nascimento<span class="txt-danger">*</span></label>
                        <input class="form-control"  type="date" name="data_nascimento" placeholder="Data de Nascimento">
                      </div>
                      <div class="col-3">
                        <label class="form-label" for="passwordwizard">Genero<span class="txt-danger">*</span></label>
                          <select class="form-select"  name="genero">
                                <option value="">Seleciona o Genero</option>
                                <option value="Masculino">Masculino</option>
                                <option value="Femenino">Femenino</option>
                          </select>
                      </div>

                      <div class="col-3">
                        <label class="form-label" for="passwordwizard">Provincia<span class="txt-danger">*</span></label>
                          <select class="form-select"  name="provincia">
                              <option value="">Escolhe a Provincia...</option>
                              @foreach($provincias as $provincia)
                                <option value="{{$provincia->id}}">{{$provincia->nome}}</option>
                              @endforeach
                          </select>
                      </div>

                      <div class="col-3">
                        <label class="form-label" for="passwordwizard">Naturalidade<span class="txt-danger">*</span></label>
                        <input class="form-control" type="text" name="naturalidade" placeholder="Naturalidade" >
                      </div>

                      <div class="col-3">
                        <label class="form-label" for="confirmpasswordwizard">Endereço/Bairro<span class="txt-danger">*</span></label>
                        <input class="form-control"  type="text" name="endereco" placeholder="Endereço/Bairro" >
                      </div>
                      <div class="col-3">
                        <label class="form-label" for="passwordwizard">Tipo Documento<span class="txt-danger">*</span></label>
                          <select class="form-select"  name="tipo_documento">
                                <option value="">Seleciona o tipo de Documento</option>
                                <option value="B.I">B.I</option>
                                <option value="Cédula">Cédula</option>
                                <option value="Boletim de Nascimento">Boletim de Nascimento</option>
                          </select>
                      </div>

                      <div class="col-3">
                        <label class="form-label" for="confirmpasswordwizard">Numero Documento</label>
                        <input class="form-control"  type="text" name="numero_documento" placeholder="Numero Documento" >
                      </div>

                      <div class="col-3">
                        <label class="form-label" for="passwordwizard">Nivel Academico<span class="txt-danger">*</span></label>
                          <select class="form-select"  name="nivel_academico">
                                <option value="">Seleciona o Nivel Academico</option>
                                <option value="Médio">Médio</option>
                                <option value="Bacharel">Bacharel</option>
                                <option value="Licenciatura">Licenciatura</option>
                                <option value="Mestre">Mestre</option>
                                <option value="Doutorado">Doutorado</option>
                          </select>
                      </div>

                      <div class="col-3">
                        <label class="form-label" for="confirmpasswordwizard">NUIT</label>
                        <input class="form-control"  type="text" name="nuit" placeholder="NUIT" >
                      </div>

                      <div class="col-3">
                        <label class="form-label" for="passwordwizard">Email<span class="txt-danger">*</span></label>
                        <input class="form-control" type="text" name="email" placeholder="Email" >
                      </div>

                      <div class="col-3">
                        <label class="form-label" for="passwordwizard">Telefone<span class="txt-danger">*</span></label>
                        <input class="form-control" type="text" name="telefone" placeholder="Telefone" >
                      </div>

                      <div class="col-3">
                          <label class="form-label" for="area_atuacao">Área de Atuação<span class="txt-danger">*</span></label>
                          <select class="form-select" name="area" id="area_atuacao">
                              <option value="">Seleciona a área de Atuação</option>
                              <option value="Informático">Informático</option>
                              <option value="Educação">Educação</option>
                              <option value="Saúde">Saúde</option>
                              <option value="Engenharia">Engenharia</option>
                              <option value="Administração">Administração</option>
                              <option value="Ciências">Ciências</option>
                              <option value="Direito">Direito</option>
                              <option value="Artes">Artes</option>
                              <option value="Agricultura">Agricultura</option>
                              <option value="Construção Civil">Construção Civil</option>
                              <option value="Economia">Economia</option>
                              <option value="Marketing">Marketing</option>
                              <option value="Gestão">Gestão</option>
                              <option value="Logística">Logística</option>
                              <option value="Recursos Humanos">Recursos Humanos</option>
                              <option value="Desenvolvimento de Software">Desenvolvimento de Software</option>
                              <option value="Eletrônica">Eletrônica</option>
                              <option value="Mecânica">Mecânica</option>
                              <option value="Meio Ambiente">Meio Ambiente</option>
                              <option value="Turismo">Turismo</option>
                              <option value="Hotelaria">Hotelaria</option>
                              <option value="Telecomunicações">Telecomunicações</option>
                          </select>
                      </div>

                      <div class="col-3">
                        <label class="form-label" for="confirmpasswordwizard">Foto Perfil</label>
                        <input class="form-control"  type="file" name="imagem" placeholder="Foto Perfil" >
                      </div>

                      <div class="col-sm-3">
                        <label class="form-label" for="firstnamewizard">Data Inicio de funções<span class="txt-danger">*</span></label>
                        <input class="form-control"  type="date" name="data_inicio_funcoes" placeholder="Data de Inicio Funcoes">
                      </div>

                      <div class="col-3">
                        <label class="form-label" for="passwordwizard">Escola<span class="txt-danger">*</span></label>
                          <select class="form-select"  name="escola">
                              <option value="">Seleciona a Escola..</option>
                              @foreach($escolas as $escola)
                                <option value="{{$escola->id}}">{{$escola->nome}} - {{$escola->codigo}}</option>
                              @endforeach
                          </select>
                      </div>

                      <div class="col-sm-3" hidden>
                        <label class="form-label" for="firstnamewizard">Salario<span class="txt-danger">*</span></label>
                        <input class="form-control"  type="text" name="salario" placeholder="Salario">
                      </div>


                </form1>
                <div class="wizard-footer d-flex gap-2 justify-content-end">
                    <button id="botao_salvar" type="submit" class="btn btn-success w-100">
                        <span id="botao_texto">{{__('Registar Professor')}}</span>
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
            nuit: {
                required: true,
            },
            escola: {
                required: true,
            },
            telefone: {
                required: true,
            }
        },
        submitHandler: function(form) {
            // Use FormData para suportar envio de arquivos
            var formData = new FormData(form);

            $.ajax({
                type: "POST",
                url: "{{ route('professor.store') }}",
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

