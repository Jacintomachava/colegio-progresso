@extends('layouts.app')

@push('css')
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
@endpush

@section('conteudo')

<div class="col-xl-12 order-md-iii">
    <div class="card title-line overflow-hidden member-wrapper">
        <div class="card-header card-no-border">
            <div class="header-top">
                <h2>
                    <img class="img-40 img-fluid m-r-20" src="../assets/images/job-search/2.jpg" alt="">
                    Professor Turmas
                </h2>
                <div class="card-header-right-icon">
                    <a href="#">
                        <button class="btn btn-pill btn-primary btn-sm" data-bs-toggle="modal" data-bs-target=".bd-example-modal-lg">Atribuir Turma ao Professor</button>
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
                            <th>Classe</th>
                            <th>Ano</th>
                            <th>Periodo</th>
                            <th>Escola</th>
                            <th>Professor</th>
                            <th>D. Turma</th>
                            <th>Acção</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach($professorTurmas as $professorTurma)
                            <tr>
                                <td></td>
                                <td>{{$professorTurma->turma->classe->nome}}</td>
                                <td>{{$professorTurma->anoAcademico->ano}}</td>
                                <td>{{$professorTurma->turma->periodo->nome}}</td>
                                <td>{{$professorTurma->turma->escola->nome}}</td>
                                <td>{{$professorTurma->professor->pessoa->nome}} </td>
                                <td>
                                    @if($professorTurma->director_turma==1)
                                      <a class="btn btn-success btn-xs" href="#!">Sim</a>
                                    @else
                                      <a class="btn btn-danger btn-xs" href="#!">Não</a>
                                    @endif
                                </td>
                                <td>
                                    <a class="btn btn-warning btn-xs" href="{{route('professorTurma.show',['turmaID'=>$professorTurma->id])}}">Disciplinas</a>
                                    <a class="btn btn-danger btn-xs activate-btn" data-id="{{ $professorTurma->id }}">
                                        Retirar Turma
                                    </a>
                                    <a class="btn btn-primary btn-xs" href="{{route('pautas.turmas',['turma'=>$professorTurma->turma_id, 'ano' => $professorTurma->anoAcademico->ano])}}">Pauta</a>
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
            <h4 class="modal-title" id="myExtraLargeModal">Atribuir Turmas ao Professor</h4>
            <button class="btn-close py-0" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body dark-modal">

            @csrf

            <div class="row">
                <div class="col-3">
                <label class="form-label" for="passwordwizard">Turmas<span class="txt-danger">*</span></label>
                    <select class="form-select"  name="turma" id="turmaSelect">
                        <option value="">Seleciona a Turma</option>
                        @foreach($turmas as $turma)
                          <option value="{{$turma->id}}">{{$turma->nome}} - {{$turma->classe->nome}} - {{$turma->escola->nome}} </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-4">
                <label class="form-label" for="passwordwizard">Professor<span class="txt-danger">*</span></label>
                    <select class="form-select"  name="professor">
                        <option value="">Seleciona o Professor</option>
                        @foreach($professores as $professor)
                          <option value="{{$professor->id}}">{{$professor->pessoa->nome}} - {{$professor->escola->nome}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-2">
                  <label class="form-label" for="passwordwizard">Director da T.?<span class="txt-danger">*</span></label>
                    <select class="form-select"  name="director" >
                        <option value="0">Não</option>
                        <option value="1">Sim</option>
                    </select>
                </div>

                <div class="col-3">
                  <label class="form-label" for="passwordwizard">Quantas Disciplinas<span class="txt-danger">*</span></label>
                    <select class="form-select"  name="todasdisciplina" id="quantidadeDisciplinas">
                        <option value="">Quantas Disciplinas</option>
                        <option value="0">Todas Disciplinas</option>
                        <option value="1">Algumas Disciplinas</option>
                    </select>
                </div>
            </div>

            <br>
            <div id="disciplinasContainer" class="row d-none">
            <hr>
                <!-- Checkboxes das disciplinas aparecerão aqui -->
            </div>

        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
            <button class="btn btn-primary" id="botao_salvar" type="submit" >
                <span id="botao_texto">{{__('Atribuir Turmas')}}</span>
                <i id="icon_enviar" class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
            </button>
        </div>
      </form>  
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
        // Adicionar regras para cada campo
        rules: {
            turma: {
                required: true
            },
            professor: {
                required: true
            },
            director: {
                required: true
            },
            todasdisciplina: {
                required: true
            }
        },
        submitHandler: function(form) {
            $.ajax({
                type: "POST",
                url: "{{route('professorTurma.store')}}",
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

<script>

document.addEventListener('DOMContentLoaded', function () {
    const turmaSelect = document.getElementById('turmaSelect');
    const quantidadeDisciplinas = document.getElementById('quantidadeDisciplinas');
    const disciplinasContainer = document.getElementById('disciplinasContainer');

    turmaSelect.addEventListener('change', function () {
        const turmaId = turmaSelect.value;

        if (turmaId) {
            fetch(`/api/classe/${turmaId}`)
                .then(response => response.json())
                .then(disciplinas => {
                    disciplinasContainer.innerHTML = ''; // Limpa os checkboxes existentes
                    disciplinas.forEach(curriculo => {
                        const disciplina = curriculo; // Acessa o objeto `disciplina`
                        if (disciplina) { // Verifica se o objeto `disciplina` existe
                            const checkbox = `
                              <div class="col-4">
                                <div class="form-check checkbox checkbox-success mb-0 ">
                                    <input class="form-check-input" name="disciplinas[]" id="disciplina-${disciplina.id}" type="checkbox" value="${disciplina.id}">
                                    <label class="form-check-label" for="disciplina-${disciplina.id}">
                                        ${disciplina.nome} ${disciplina.co_curricular == 1 ? ' - CC' : ' - C'}
                                    </label>
                                 </div>
                              </div>
                            `;
                            disciplinasContainer.innerHTML += checkbox;
                        }
                    });
                    disciplinasContainer.classList.remove('d-none'); // Exibe o container
                });
        } else {
            disciplinasContainer.innerHTML = ''; // Limpa os checkboxes caso a turma seja desmarcada
            disciplinasContainer.classList.add('d-none'); // Esconde o container
        }
    });

    quantidadeDisciplinas.addEventListener('change', function () {
        const value = quantidadeDisciplinas.value;

        if (value === '0') {
            // Ocultar disciplinas (todas selecionadas)
            disciplinasContainer.classList.add('d-none');
        } else if (value === '1') {
            // Mostrar disciplinas para seleção
            disciplinasContainer.classList.remove('d-none');
        }
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
                    text: "Você deseja retirar esta Turma ao Professor? ",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sim, Retira!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Enviar requisição AJAX
                        fetch(`/retirar/turma/${id}`, {
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

@endpush
