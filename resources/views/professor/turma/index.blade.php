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
                    <img class="img-40 img-fluid m-r-20" src="{{ URL('/public/assets/images/job-search/2.jpg') }}" alt="">
                    Minhas Turmas 
                </h2>
                <div class="card-header-right-icon">
                        <h2>Ano Lectivo Activo {{$anoAtivo->ano}}</h2>
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
                            <th>Escola</th>
                            <th>Ano Lectivo</th>
                            <th>D. Turma</th>
                            <th>Pauta F.</th>
                            <th>Exame</th>
                            <th>Acção</th>
                        </tr>
                    </thead>
                    <tbody>

@php
    $professorTurmasOrdenadas = $professorTurmas->sortBy(function ($item) {
        return $item->turma->classe->nome;
    });

    $classesExibidas = [];
@endphp

@foreach($professorTurmasOrdenadas as $professorTurma)
    @php
        $chaveClasseAno = $professorTurma->turma->classe_id . '-' . $professorTurma->ano_academico_id;
    @endphp

    @if(!in_array($chaveClasseAno, $classesExibidas))
        @php $classesExibidas[] = $chaveClasseAno; @endphp

        <tr>
            <td></td>
            <td>{{ $professorTurma->turma->classe->nome }}</td>
            <td>{{ $professorTurma->turma->escola->nome }}</td>
            <td>{{ $professorTurma->anoAcademico->ano }}</td>
            <td>
                @if($professorTurma->director_turma_id == null)
                    ...
                @else
                    {{$professorTurma->directorTurma->pessoa->nome}}
                @endif
            </td>
            <td>
                @if($professorTurma->pauta_finalizada == 1)
                    <span style="color: blue">Validada</span>
                @else
                    <span style="color: red">Pendente</span>
                @endif
            </td>
            <td>
                @if($professorTurma->turma->classe->tem_exame == 1)
                    
                    @if($professorTurma->pauta_finalizada == 0)
                      <a class="btn btn-success btn-xs" href="{{ route('pautas.exames', ['classe' => $professorTurma->turma->classe->id,'disciplina'=>1,'ano' => $professorTurma->anoAcademico->ano]) }}">Ver Exame</a>
                    @else
                      <a class="btn btn-success btn-xs" href="#">Exame Val.</a>
                    @endif
                @else
                    <span style="color: red">Sem Exame</span>
                @endif
            </td>
            <td>
                {{-- 

                    <a class="btn btn-warning btn-xs" href="{{ route('professorTurma.show', ['turmaID' => $professorTurma->id,'ano' => $professorTurma->anoAcademico->ano]) }}">Ver Disciplina</a>
                    
                    --}}

                @if($professorTurma->pauta_finalizada == 0)
                  <a class="btn btn-info btn-xs btn-validar-pauta"  data-classe="{{ $professorTurma->turma->classe->id }}" data-ano="{{ $professorTurma->anoAcademico->ano }}" >V. Pauta</a>
                @elseif(Auth::user()->codigo == '02.P006.25' && $professorTurma->pauta_finalizada == 1)
                      <a class="btn btn-warning btn-xs btn-invalidar-pauta"
                        data-classe="{{ $professorTurma->turma->classe->id }}"
                        data-ano="{{ $professorTurma->anoAcademico->ano }}">
                            Invalidar
                      </a>
                @endif


                @if($professorTurma->pauta_finalizada == 0)
                  <a class="btn btn-danger btn-xs" href="{{ route('lancar.notas', ['classe' => $professorTurma->turma->classe->id,'disciplina'=>1,'trimestre'=>1,'ano' => $professorTurma->anoAcademico->ano]) }}">L. Notas</a>
                @else
                  <a class="btn btn-danger btn-xs" href="#">Validada</a>
                @endif
                <a class="btn btn-warning btn-xs" href="{{ route('notas.show', ['turmaID' => $professorTurma->turma->classe->id,'ano' => $professorTurma->anoAcademico->ano]) }}">Deck</a>
                <a class="btn btn-primary btn-xs" href="{{ route('pauta.turma', ['turma' => $professorTurma->turma->classe->id,'ano' => $professorTurma->anoAcademico->ano]) }}">Pauta</a>
            </td>
        </tr>
    @endif
@endforeach



                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>


@endsection

@push('js')

<script>
document.querySelectorAll('.btn-validar-pauta').forEach(btn => {
    btn.addEventListener('click', function () {

        let classeId = this.dataset.classe;
        let ano = this.dataset.ano;

        Swal.fire({
            title: 'Validar pauta?',
            text: 'Após validar, os alunos terão nova classe e serão geradas mensalidades. Confirma que todas as notas já foram lançadas?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim, validar',
            cancelButtonText: 'Cancelar',
        }).then((result) => {

            if (result.isConfirmed) {

                Swal.fire({
                    title: 'Processando...',
                    text: 'A validar pauta, aguarde...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch(`/pauta/validar/${classeId}/${ano}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {

                    Swal.close();

                    Swal.fire({
                        icon: data.status,
                        title: data.title,
                        text: data.message
                    });

                    if (data.status === 'success') {
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    }
                })
                .catch(() => {
                    Swal.fire(
                        'Erro',
                        'Erro ao comunicar com o servidor.',
                        'error'
                    );
                });
            }
        });
    });
});
</script>


<script>
document.querySelectorAll('.btn-invalidar-pauta').forEach(btn => {

    btn.addEventListener('click', function () {

        let classeId = this.dataset.classe;
        let ano = this.dataset.ano;

        Swal.fire({
            title: 'Invalidar pauta?',
            text: 'Tem certeza que deseja invalidar esta pauta?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim, invalidar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#d33'
        }).then((result) => {

            if (result.isConfirmed) {

                Swal.fire({
                    title: 'Processando...',
                    text: 'A invalidar pauta, aguarde...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch(`/pauta/invalidar/${classeId}/${ano}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(async response => {

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(
                            data.message || 'Não foi possível invalidar a pauta.'
                        );
                    }

                    return data;
                })
                .then(data => {

                    Swal.fire({
                        icon: data.status,
                        title: data.title,
                        text: data.message
                    });

                    if (data.status === 'success') {

                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    }
                })
                .catch(error => {

                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: error.message
                    });

                });
            }
        });
    });
});
</script>

@endpush
