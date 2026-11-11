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
                    <img class="img-40 img-fluid m-r-20" src="{{ URL('/public/assets/images/job-search/2.jpg')}}" alt="">
                    Funcionarios  
                </h2>
                <div class="card-header-right-icon">
                    <a href="{{route('funcionario.create')}}">
                        <button class="btn btn-pill btn-primary btn-sm">Cadastrar Funcionarios</button>
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
                            <th>Cargo</th>
                            <th>Estado</th>
                            <th>Telefone</th>
                            <th>Acção</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                        @foreach($funcionarios as $funcionario)
                            <tr>
                                <td></td>
                                <td>{{$funcionario->codigo}}</td>
                                <td>{{$funcionario->pessoa->nome}}</td>
                                <td>{{$funcionario->pessoa->genero}}</td>
                                <td>{{$funcionario->cargo}}</td>
                                <td>
                                    @if($funcionario->ex_funcionario==0)
                                        <a class="btn btn-success btn-xs" href="#!">Acivo</a>   
                                    @else
                                        <a class="btn btn-danger btn-xs" href="#!">Ex-Funcionario</a>
                                    @endif
                                </td>
                                <td>{{$funcionario->pessoa->telefone}}</td>
                                <td>
                                    <a class="btn btn-primary btn-xs activate-btn" href="{{route('funcionario.show',['codigo'=>$funcionario->codigo])}}">
                                        Detalhes
                                    </a>
                                    @if($funcionario->cargo=='Professor')
                                        <a class="btn btn-warning btn-xs activate-btn" href="{{route('professor.edit',['codigo'=>$funcionario->codigo])}}">
                                            Editar
                                        </a>
                                    @else
                                        <a class="btn btn-warning btn-xs activate-btn" href="{{route('funcionario.edit',['codigo'=>$funcionario->codigo])}}">
                                            Editar
                                        </a>
                                    @endif

                                    <a class="btn btn-danger btn-xs activate-btn" data-id="{{ $funcionario->id }}" >
                                        Remover
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
                    text: "Já não estara mais na escola? ",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sim, Retira!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Enviar requisição AJAX
                        fetch(`/retirar/funcionario/${id}`, {
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
