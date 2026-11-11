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
                    Utilizadores
                </h2>
                <div class="card-header-right-icon">
                    <center>...</center>
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
                            <th>Escola</th>
                            <th>User</th>
                            <th>Estado</th>
                            <th>Permissão</th>
                            <th>Acção</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach($users as $user)
                            <tr>
                                <td></td>
                                <td>{{$user->pessoa->nome}}</td>
                                <td>{{$user->escola->codigo}}</td>
                                <td>{{$user->telefone}}</td>
                                <td>
                                   @if($user->estado==1)
                                        <a class="btn btn-success btn-xs activate-btn" >
                                            Activo
                                        </a>
                                    @else
                                        <a class="btn btn-info btn-xs activate-btn" >
                                            Desativo
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    @foreach($user->roles as $role)
                                        {{ $role->name }},
                                    @endforeach
                                </td>

                                <td>
                                    <a class="btn btn-warning btn-xs activate-btn" href="{{route('user.edit',['codigo'=>$user->codigo])}}" >
                                        Editar
                                    </a>
                                     <a class="btn btn-danger btn-xs activate-btn" onclick="reporSenha({{ $user->id }}, '{{ $user->pessoa->nome }}')" >
                                        Repor Senha
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
    function reporSenha(userId, nome) {
        Swal.fire({
            title: `Repor senha de ${nome}?`,
            text: "A nova senha será igual a username.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, repor!'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/repor/senha/${userId}`, {
                    method: 'get',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error("Erro ao repor senha.");
                    return response.json();
                })
                .then(data => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Senha reposta com sucesso!',
                        text: `Nova senha é o username`
                    });
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
    }
</script>

@endpush
