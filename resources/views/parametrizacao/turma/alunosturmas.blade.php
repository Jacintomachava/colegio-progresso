@extends('layouts.app')

@push('css')
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
</head>

@endpush

@section('conteudo')

<div class="col-xl-12 order-md-iii">
    <div class="card title-line overflow-hidden member-wrapper">
        <div class="card-header card-no-border">
            <div class="header-top">
                <h2>
                    <img class="img-40 img-fluid m-r-20" src="../assets/images/job-search/2.jpg" alt="">
                    Alunos da turma {{$turmas->nome}},    {{$turmas->classe->nome}} - {{$turmas->periodo->nome}} - {{$turmas->classe->ciclo->nome}} - {{$turmas->anoAcademico->ano}}
                </h2>
                <div class="card-header-right-icon">
                    <a href="#" data-bs-toggle="modal" data-bs-target=".bd-example-modal-lg">
                        <button class="btn btn-pill btn-success btn-sm">Atribuir Turma</button>
                    </a>
                </div>
            </div>
        </div>
    </div> <!-- Fechamento da div "card title-line overflow-hidden member-wrapper" -->
</div> <!-- Fechamento da div "col-xl-12 order-md-iii" -->

<div class="col-sm-12" style="margin-top: -2%">
    <div class="card">
        <div class="card-header pb-0 card-no-border">
        </div>
        <div class="card-body">

            <div class="table-responsive custom-scrollbar">
                <table class="display" id="basic-1">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Codigo</th>
                            <th>Nome</th>
                            <th>Genero</th>
                            <th>Classe</th>
                            <th>Ciclo</th>
                            <th>Estado</th>
                            <th>Data R.</th>
                            <th>Acção</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach($matriculaAlunoTurmas as $matriculaAlunoTurma)
                            <tr>
                                <td></td>
                                <td>{{$matriculaAlunoTurma->aluno->codigo}}</td>
                                <td>{{$matriculaAlunoTurma->aluno->pessoa->nome}}</td>
                                <td>{{$matriculaAlunoTurma->aluno->pessoa->genero}}</td>
                                <td>{{$matriculaAlunoTurma->aluno->classe->nome}}</td>
                                <td>{{$matriculaAlunoTurma->aluno->classe->ciclo->nome}}</td>
                                <td>
                                    @if($matriculaAlunoTurma->aluno->suspenso==1)
                                        <a class="btn btn-danger btn-xs" href="#!">Suspenso</a>
                                    @else
                                        <a class="btn btn-success btn-xs" href="#!">Activo</a>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($matriculaAlunoTurma->aluno->created_at)->format('d-M-Y') }}</td>
                                <td>
                                    <a class="btn btn-primary btn-xs activate-btn" href="{{route('aluno.show',['codigo'=>$matriculaAlunoTurma->aluno->codigo])}}" >
                                        Detalhes
                                    </a>
                                </td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModal" aria-hidden="true">
    <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form  id="form">  
        <div class="modal-header">
            <h4 class="modal-title" id="myExtraLargeModal">Atribuir Turmas ao Aluno</h4>
            <button class="btn-close py-0" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body dark-modal">

            @csrf
            
            <div class="row">
                <div class="col-sm-6">
                    <label class="form-label" for="firstnamewizard">Turma<span class="txt-danger">*</span></label>
                    <input class="form-control"  type="text" disabled name="indicacaoespecial"  value="Turma {{$turmas->nome}},    {{$turmas->classe->nome}} - {{$turmas->periodo->nome}} - {{$turmas->classe->ciclo->nome}}">
                </div>
                <div class="col-sm-3">
                    <label class="form-label" for="firstnamewizard">Disponível<span class="txt-danger">*</span></label>
                    <input class="form-control"  type="text" disabled name="vaga_disponivel" value="{{$turmas->vaga}} Vagas">
                </div>
                <div class="col-sm-3">
                    <label class="form-label" for="firstnamewizard">Turma {{$turmas->nome}} Tem  <span class="txt-danger">*</span></label>
                    <input class="form-control"  type="text" disabled name="aluno_matriculado" value="{{$turmas->matriculado}} Alunos">
                </div>
                <div class="col-sm-12" hidden>
                    <label class="form-label" for="firstnamewizard">Turma<span class="txt-danger">*</span></label>
                    <input class="form-control"  type="text" name="turma" value="{{$turmas->id}}">
                </div>
                <div class="col-sm-12" hidden>
                    <label class="form-label" for="firstnamewizard">Turma<span class="txt-danger">*</span></label>
                    <input class="form-control"  type="text" name="turma" value="{{$turmas->id}}">
                </div>
            </div>    

            <br>
            <div class="form-group col-4">
                <label for="contactos" class="form-label">Alunos da {{$turmas->classe->nome}}<span class="txt-danger">*</span></label>
                <select class="form-select contactos" name="alunos[]"  multiple="multiple" required>
                    @foreach ($alunosSemTurmas as $alunoMatricula)
                        <option value="{{ $aluno->id }}"
                            {{ in_array($alunoMatricula->aluno->id, old('alunos', [])) ? 'selected' : '' }}>
                            {{ $alunoMatricula->aluno->pessoa->nome }} ({{ $alunoMatricula->aluno->codigo }})
                        </option>
                    @endforeach
                </select>
            </div>

            <br>

        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
            <button class="btn btn-primary" id="botao_salvar" type="submit" >
                <span id="botao_texto">{{__('Atribuir Turma')}}</span>
                <i id="icon_enviar" class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
            </button>
        </div>
      </form>  
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
            placeholder: "{{ __('Seleciona Alunos Irmaos') }}",
            allowClear: true
        });
    });
    $(document).ready(function() {
        $('.contactos1').select2({
            placeholder: "{{ __('Seleciona Actividades Co-Curricular') }}",
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
        // Adicionar regras para cada campo
        rules: {
            alunos: {
                required: true
            }
        },
        submitHandler: function(form) {
            $.ajax({
                type: "POST",
                url: "{{route('atribuirTurma.store')}}",
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

@endpush
