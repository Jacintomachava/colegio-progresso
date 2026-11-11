@extends('layouts.app')

@push('css')
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
@endpush

@section('conteudo')

<div class="col-xl-12 order-md-iii">
    <div class="card title-line overflow-hidden member-wrapper">
        <div class="card-header card-no-border">
            <div class="header-top">
                <h2>
                    <img class="img-40 img-fluid m-r-20" src="../assets/images/job-search/2.jpg" alt="">
                    Disciplinas da Turma - {{$turmaProfessor->turma->nome}}
                </h2>
                <div class="card-header-right-icon">
                    <a href="#" data-bs-toggle="modal" data-bs-target=".bd-example-modal-lg">
                        <button class="btn btn-pill btn-success btn-sm">Atribuir Disciplinas</button>
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
                            <th>Disciplinas</th>
                            <th>Turma</th>
                            <th>Classe</th>
                            <th>Professor</th>
                            <th>Ano Lectivo</th>
                            <th>Acção</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach($turmaProfessorDisciplinas as $turmaProfessorDisciplina)
                            <tr>
                                <td></td>
                                <td>{{$turmaProfessorDisciplina->disciplina->nome}}</td>
                                <td>{{$turmaProfessorDisciplina->turma->nome}}</td>
                                <td>{{$turmaProfessorDisciplina->classe->nome}}</td>
                                <td>{{$turmaProfessorDisciplina->professor->pessoa->nome}}</td>
                                <td>{{$turmaProfessorDisciplina->anoAcademico->nome}}</td>
                                <td>
                                    <a class="btn btn-danger btn-xs activate-btn" data-id="{{ $turmaProfessorDisciplina->id }}">
                                        Retirar Disciplina
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
            <h4 class="modal-title" id="myExtraLargeModal">Adicionar Disciplinas ao <i>professor</i> <i><u>{{$turmaProfessor->professor->pessoa->nome}}</u></i> na Turma <i><u>{{$turmaProfessor->turma->nome}}</u></i></h4>
            <button class="btn-close py-0" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body dark-modal">

            @csrf
            
            <input class="form-control" type="hidden" name="turma"  value="{{$turmaProfessor->turma_id}}" >
            <input class="form-control" type="hidden" name="professor"  value="{{$turmaProfessor->professor_id}}" >
            <input class="form-control" type="hidden" name="professorTurma"  value="{{$turmaProfessor->id}}" >

            <div class="row">
                <div class="form-group col-12">
                    <label for="contactos" class="form-label">{{ __('Disciplinas Nao Atribuidas') }} <span class="txt-danger">*</span></label>
                    <select class="form-select contactos1" name="disciplinas[]"  multiple="multiple" required>
                        @foreach ($disciplinasNaoAtribuidas as $disciplina)
                            <option value="{{ $disciplina->id }}"
                                {{ in_array($disciplina->id, old('cocurriculares', [])) ? 'selected' : '' }}>
                                {{ $disciplina->nome }} 
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <br>

        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
            <button class="btn btn-primary" id="botao_salvar" type="submit" >
                <span id="botao_texto">{{__('Adicionar Disciplinas')}}</span>
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
    document.addEventListener('DOMContentLoaded', function () {
        const activateButtons = document.querySelectorAll('.activate-btn');

        activateButtons.forEach(button => {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');

                Swal.fire({
                    title: 'Tem certeza?',
                    text: "Você deseja retirar esta Disciplina ao Professor? ",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sim, Retira!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Enviar requisição AJAX
                        fetch(`/retirar/disciplina/${id}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire(
                                    'Retirado!',
                                    data.message,
                                    'success'
                                );
                                // Recarregar a página para atualizar o estado
                                location.reload();
                            } else {
                                Swal.fire(
                                    'Erro!',
                                    data.message,
                                    'error'
                                );
                            }
                        })
                        .catch(() => {
                            Swal.fire(
                                'Erro!',
                                'Ocorreu um erro ao processar sua solicitação.',
                                'error'
                            );
                        });
                    }
                });
            });
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
            disciplina: {
                required: true
            }
        },
        submitHandler: function(form) {
            $.ajax({
                type: "POST",
                url: "{{route('professor.disciplinas')}}",
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
