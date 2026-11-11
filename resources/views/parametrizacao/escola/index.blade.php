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
                    Escola
                </h2>
                <div class="card-header-right-icon" hidden>
                    <a href="{{route('escola.create')}}">
                        <button class="btn btn-pill btn-primary btn-sm">Adicionar Escola</button>
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
                            <th>Nome</th>
                            <th>Tipo</th>
                            <th>Telefone1</th>
                            <th>Telefone2</th>
                            <th>Endereco</th>
                            <th>Codigo</th>
                            <th>Slogan</th>
                            <th>Editar</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach($escolas as $escola)
                            <tr>
                                <td>
                                    <img class="img-fluid" src="{{ URL('/public/logotipo/'.$escola->logotipo ) }}" width="120" height="120"  alt="client">
                                </td>
                                <td>{{$escola->nome}}</td>
                                <td>{{$escola->tipo}}</td>
                                <td>{{$escola->telefone}}</td>
                                <td>{{$escola->telefone2}}</td>
                                <td>{{$escola->endereco}} </td>
                                <td>{{$escola->codigo}}</td>
                                <td>{{$escola->slogan}}</td>
                                <td>
                                    <a class="btn btn-primary btn-xs" href="{{route('escola.edit',['codigo'=>$escola->id])}}" >
                                        Editar
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
                        fetch(`/anos-academicos/${id}/ativar`, {
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
