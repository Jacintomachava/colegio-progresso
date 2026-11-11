@extends('layouts.app')

@push('css')
@endpush

@section('conteudo')

  <div class="container-fluid">
    <div class="row">
      <div class="col-xl-12">
        <div class="card height-equal title-line">
          <div class="card-header">
            <h2>Editar Professor</h2>
            <p class="f-m-light mt-1">
                Editar do Professor
            </p>
          </div>
          <div class="card-body basic-wizard important-validation">
            <div id="msform1">
              <form class="row g-3 needs-validation custom-input" id="form" enctype="multipart/form-data">

                @csrf
                
                <form1 class="stepper-one row g-3 needs-validation custom-input" >

                    <input class="form-control"  type="hidden" name="codigo" value="{{$professor->codigo}}" >

                    <div class="col-sm-3">
                        <label class="form-label" for="email-basic-wizard">Nome de Professor<span class="txt-danger">*</span></label>
                        <input class="form-control"  type="text" name="nome" value="{{$professor->pessoa->nome}}" placeholder="Nome de Professor">
                      </div>
                      <div class="col-sm-3">
                        <label class="form-label" for="firstnamewizard">Data de Nascimento<span class="txt-danger">*</span></label>
                        <input class="form-control"  type="date" name="data_nascimento" value="{{$professor->pessoa->data_nascimento}}" placeholder="Data de Nascimento">
                      </div>
                      <div class="col-3">
                        <label class="form-label" for="passwordwizard">Genero<span class="txt-danger">*</span></label>
                          <select class="form-select"  name="genero">
                                <option value="">Seleciona o Genero</option>
                                <option value="Masculino" @if($professor->pessoa->genero=='Masculino') selected @endif>Masculino</option>
                                <option value="Femenino" @if($professor->pessoa->genero=='Femenino') selected @endif>Femenino</option>
                          </select>
                      </div>

                      <div class="col-3">
                        <label class="form-label" for="passwordwizard">Provincia <span class="txt-danger">*</span></label>
                          <select class="form-select"  name="provincia">
                              <option value="">Escolhe a Provincia...</option>
                              @foreach($provincias as $provincia)
                                <option value="{{$provincia->id}}" @if($funcionario->provincia_id == $provincia->id) selected @endif>
                                    {{$provincia->nome}}
                                </option>
                              @endforeach
                          </select>
                      </div>

                      <div class="col-3">
                        <label class="form-label" for="passwordwizard">Naturalidade<span class="txt-danger">*</span></label>
                        <input class="form-control" type="text" name="naturalidade" value="{{$professor->pessoa->naturalidade}}"  placeholder="Naturalidade" >
                      </div>

                      <div class="col-3">
                        <label class="form-label" for="confirmpasswordwizard">Endereço/Bairro<span class="txt-danger">*</span></label>
                        <input class="form-control"  type="text" name="endereco" value="{{$professor->pessoa->endereco}}" placeholder="Endereço/Bairro" >
                      </div>
                      <div class="col-3">
                        <label class="form-label" for="passwordwizard">Tipo Documento<span class="txt-danger">*</span></label>
                          <select class="form-select"  name="tipo_documento">
                                <option value="">Seleciona o tipo de Documento</option>
                                <option value="B.I" @if($professor->pessoa->tipo_documento=='B.I') selected @endif>B.I</option>
                                <option value="Cédula" @if($professor->pessoa->tipo_documento=='Cédula') selected @endif>Cédula</option>
                                <option value="Boletim de Nascimento" @if($professor->pessoa->tipo_documento=='Boletim de Nascimento') selected @endif>Boletim de Nascimento</option>
                          </select>
                      </div>

                      <div class="col-3">
                        <label class="form-label" for="confirmpasswordwizard">Numero Documento</label>
                        <input class="form-control"  type="text" name="numero_documento" value="{{$professor->pessoa->numero_documento}}" placeholder="Numero Documento" >
                      </div>

                      <div class="col-3">
                        <label class="form-label" for="passwordwizard">Nivel Academico<span class="txt-danger">*</span></label>
                          <select class="form-select"  name="nivel_academico">
                                <option value="">Seleciona o Nivel Academico</option>
                                <option value="Médio" @if($professor->pessoa->nivel_academico=='Médio') selected @endif>Médio</option>
                                <option value="Bacharel" @if($professor->pessoa->nivel_academico=='Bacharel') selected @endif>Bacharel</option>
                                <option value="Licenciatura" @if($professor->pessoa->nivel_academico=='Licenciatura') selected @endif>Licenciatura</option>
                                <option value="Mestre" @if($professor->pessoa->nivel_academico=='Mestre') selected @endif>Mestre</option>
                                <option value="Doutorado" @if($professor->pessoa->nivel_academico=='Doutorado') selected @endif>Doutorado</option>
                          </select>
                      </div>

                      <div class="col-3">
                        <label class="form-label" for="confirmpasswordwizard">NUIT</label>
                        <input class="form-control"  type="text" name="nuit"  value="{{$professor->pessoa->nuit}}" placeholder="NUIT" >
                      </div>

                      <div class="col-3">
                        <label class="form-label" for="passwordwizard">Email<span class="txt-danger">*</span></label>
                        <input class="form-control" type="text" name="email"  value="{{$professor->pessoa->email}}" placeholder="Email" >
                      </div>

                      <div class="col-3">
                        <label class="form-label" for="passwordwizard">Telefone<span class="txt-danger">*</span></label>
                        <input class="form-control" type="text" name="telefone"  value="{{$professor->pessoa->telefone}}" placeholder="Telefone" >
                      </div>

                      <div class="col-3">
                          <label class="form-label" for="area_atuacao">Área de Atuação<span class="txt-danger">*</span></label>
                          <select class="form-select" name="area" id="area_atuacao">
                              <option value="">Seleciona a área de Atuação</option>
                              <option value="Informático" @if($funcionario->area=="Informático") selected @endif>Informático</option>
                              <option value="Educação" @if($funcionario->area=="Educação") selected @endif>Educação</option>
                              <option value="Saúde" @if($funcionario->area=="Saúde") selected @endif>Saúde</option>
                              <option value="Engenharia" @if($funcionario->area=="Engenharia") selected @endif>Engenharia</option>
                              <option value="Administração" @if($funcionario->area=="Administração") selected @endif>Administração</option>
                              <option value="Ciências" @if($funcionario->area=="Ciências") selected @endif>Ciências</option>
                              <option value="Direito" @if($funcionario->area=="Direito") selected @endif>Direito</option>
                              <option value="Artes" @if($funcionario->area=="Artes") selected @endif>Artes</option>
                              <option value="Agricultura" @if($funcionario->area=="Agricultura") selected @endif>Agricultura</option>
                              <option value="Construção Civil" @if($funcionario->area=="Construção Civil") selected @endif>Construção Civil</option>
                              <option value="Economia" @if($funcionario->area=="Economia") selected @endif>Economia</option>
                              <option value="Marketing" @if($funcionario->area=="Marketing") selected @endif>Marketing</option>
                              <option value="Gestão" @if($funcionario->area=="Gestão") selected @endif>Gestão</option>
                              <option value="Logística" @if($funcionario->area=="Logística") selected @endif>Logística</option>
                              <option value="Recursos Humanos" @if($funcionario->area=="Recursos Humanos") selected @endif>Recursos Humanos</option>
                              <option value="Desenvolvimento de Software" @if($funcionario->area=="Desenvolvimento de Software") selected @endif>Desenvolvimento de Software</option>
                              <option value="Eletrônica" @if($funcionario->area=="Eletrônica") selected @endif>Eletrônica</option>
                              <option value="Mecânica" @if($funcionario->area=="Mecânica") selected @endif>Mecânica</option>
                              <option value="Meio Ambiente" @if($funcionario->area=="Meio Ambiente") selected @endif>Meio Ambiente</option>
                              <option value="Turismo" @if($funcionario->area=="Turismo") selected @endif>Turismo</option>
                              <option value="Hotelaria" @if($funcionario->area=="Hotelaria") selected @endif>Hotelaria</option>
                              <option value="Telecomunicações" @if($funcionario->area=="Telecomunicações") selected @endif>Telecomunicações</option>
                          </select>
                      </div>

                      <div class="col-3">
                        <label class="form-label" for="confirmpasswordwizard">Foto Perfil</label>
                        <input class="form-control"  type="file" name="imagem" placeholder="Foto Perfil" >
                      </div>

                      <div class="col-sm-3">
                        <label class="form-label" for="firstnamewizard">Data Inicio de funções<span class="txt-danger">*</span></label>
                        <input class="form-control"  type="date" name="data_inicio_funcoes"  value="{{$funcionario->data_inicio_funcoes}}" placeholder="Data de Inicio Funcoes">
                      </div>

                      <div class="col-3">
                        <label class="form-label" for="passwordwizard">Escola<span class="txt-danger">*</span></label>
                          <select class="form-select"  name="escola">
                              <option value="">Seleciona a Escola..</option>
                              @foreach($escolas as $escola)
                                <option value="{{$escola->id}}" @if($funcionario->escola_id==$escola->id) selected @endif>{{$escola->nome}} - {{$escola->codigo}}</option>
                              @endforeach
                          </select>
                      </div>

                      <div class="col-sm-3" hidden>
                        <label class="form-label" for="firstnamewizard">Salario<span class="txt-danger">*</span></label>
                        <input class="form-control"  type="text" name="salario" value="{{$funcionario->salario}}"   placeholder="Salario">
                      </div>


                </form1>
                <div class="wizard-footer d-flex gap-2 justify-content-end">
                    <button id="botao_salvar" type="submit" class="btn btn-success w-100">
                        <span id="botao_texto">{{__('Editar Professor')}}</span>
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
                url: "{{ route('professor.update') }}",
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

