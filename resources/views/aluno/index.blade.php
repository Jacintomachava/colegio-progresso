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
                    Alunos
                </h2>
                <div class="card-header-right-icon">
                    ...
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
                            <th>Estado</th>
                            <th>Tel. Enc. </th>
                            <th>Acção</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach($alunos as $aluno)
                            <tr>
                                <td></td>
                                <td><a target="_blank" href="{{route('aluno.cartao',['codigo'=>$aluno->codigo])}}">{{$aluno->codigo}}</a></td>
                                <td data-container="body" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="{{ $aluno->pessoa->nome }}">
                                    {{ \Illuminate\Support\Str::limit($aluno->pessoa->nome, 25) }}
                                </td>
                                <td>{{$aluno->pessoa->genero}}</td>
                                <td>{{$aluno->classe->nome}}</td>
                                <td>
                                    @if($aluno->desistiu==1)
                                        <a class="btn btn-danger btn-xs" href="#!">Ex-aluno</a>
                                    @else
                                        <a class="btn btn-success btn-xs" href="#!">Acivo</a>
                                    @endif
                                </td>
                                <td>{{ optional($aluno->encarregado->first())->telefone }}</td>
                                <td>
                                    <a class="btn btn-primary btn-xs" href="{{route('aluno.show',['codigo'=>$aluno->codigo])}}" >
                                        Detalhes
                                    </a>
                                    <a class="btn btn-warning btn-xs" href="{{route('aluno.edit',['codigo'=>$aluno->codigo])}}" >
                                        Editar 
                                    </a>
                                    <a class="btn btn-danger btn-xs activate-btn" data-id="{{ $aluno->id }}" >
                                        Remover
                                    </a>
                                    <a class="btn btn-warning btn-xs" href="{{route('mudar.telefone',['codigo'=>$aluno->codigo])}}" >
                                        Mudar Contacto
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
                    text: "Já não está mais na escola? ",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sim, Retira!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Enviar requisição AJAX
                        fetch(`/retirar/aluno/${id}`, {
                            method: 'get',
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
