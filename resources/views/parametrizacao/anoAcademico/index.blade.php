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
                    <img class="img-40 img-fluid m-r-20" src="{{ URL('/assets/images/job-search/2.jpg') }}" alt="">
                    Ano Acadêmico
                </h2>
                <div class="card-header-right-icon">
                    <a href="{{route('create.academico')}}">
                        <button class="btn btn-pill btn-primary btn-sm">Adicionar Ano Acadêmico</button>
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
                            <th>Ano</th>
                            <th>Trimestre</th>
                            <th>Inicio Renovação</th>
                            <th>Fim Renovação</th>
                            <th>Data Lanc. Nota</th>
                            <th>R. Final</th>
                            <th>SMS</th>
                            <th>Acções</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach($anosAcademicos as $anoAcademico)
                            <tr>
                                <td></td>
                                <td>{{$anoAcademico->ano}}</td>
                                <td>
                                    @if($anoAcademico->trimestre==1)
                                      1° Tri
                                    @elseif($anoAcademico->trimestre==2)
                                      2° Tri
                                    @elseif($anoAcademico->trimestre==3)
                                      3° Tri
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($anoAcademico->data_inicio_renovacao)->format('d-M-Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($anoAcademico->data_fim_renovacao)->format('d-M-Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($anoAcademico->data_fim_lancamento_pauta)->format('d-M-Y') }}</td>
                                <td>
                                    @if($anoAcademico->pauta_finalizada==1)
                                      <a class="btn btn-success btn-xs" href="#!">Divulgado</a>
                                    @else
                                      <a class="btn btn-danger btn-xs" href="#!">Pendente</a>
                                    @endif
                                </td>
                
                                <td>
                                    @if($anoAcademico->sms==1)
                                      <a class="btn btn-success btn-xs" href="#!">Activo</a>
                                    @else
                                      <a class="btn btn-danger btn-xs" href="#!">Inactivo</a>
                                    @endif
                                </td>
                                <td>
                                    @if($anoAcademico->estado==1)
                                        <a class="btn btn-info btn-xs" href="{{route('taxa.edit',['id'=>$anoAcademico->id ])}}" >
                                            Editar
                                        </a>
                                    @else
                                        <a class="btn btn-warning btn-xs activate-btn" data-id="{{ $anoAcademico->id }}">
                                            Activar
                                        </a>

                                    @endif
                                </td>
                            </tr>
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
    document.addEventListener('DOMContentLoaded', function () {
        const activateButtons = document.querySelectorAll('.activate-btn');

        activateButtons.forEach(button => {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');

                Swal.fire({
                    title: 'Tem certeza?',
                    text: "Você deseja ativar este ano acadêmico? Os outros serão desativados.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sim, ativar!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Enviar requisição AJAX
                        fetch(`/escola/activar/anos-academicos/${id}`, {
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
                                    'Ativado!',
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
