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
                    Transferencia
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
                            <th> </th>
                            <th> Alunos</th>
                            <th> Data Trans.</th>
                            <th> E. Origem</th>
                            <th> E. Destino</th>
                            <th> Motivo</th>
                            <th> Data</th>
                            <th> Hora</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach($transferencias as $transferencia)
                            <tr>
                                <td> </td>
                                <td> {{$transferencia->aluno->pessoa->nome}} </td>
                                <td> {{\Carbon\Carbon::parse($transferencia->data_transferencia)->format('d-M-Y')}} </td>
                                <td> {{$transferencia->escolaOrigem->nome }} </td>
                                <td> {{$transferencia->escolaDestino->nome }} </td>
                                <td data-container="body" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="{{$transferencia->motivo}}">
                                    {{ Str::limit($transferencia->motivo, 10) }}
                                </td>
                                <td> {{\Carbon\Carbon::parse($transferencia->updated_at)->format('d-M-Y')}} </td>
                                <td> {{ \Carbon\Carbon::parse($transferencia->updated_at)->format('H:i') }} </td>
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



@endpush
