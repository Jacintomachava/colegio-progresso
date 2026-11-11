
@extends('layouts.app')

@push('css')

<style>
    
    .btn-add-class {
        margin: 10px 10px 0 20px;
    }
    .modal-header {
        padding: 15px 15px 30px 15px
    }
    .column-data-pag {
        width: 30%;
    }
        
</style>
@endpush

@section('conteudo')

<div class="main-container">
    <form id="form-pagamento">
        @csrf

        <div class="container-fluid">
            <div class="card-body">

                <div class="col-xxl-12" style="margin-top: 0.5%"> 
                    <div class="card title-line">
                        <div class="card-body"> 
                            <ul class="nav nav-tabs border-tab mb-0" id="bottom-tab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link nav-border txt-info tab-info active" id="bottom-inbox-tab" data-bs-toggle="tab" href="#bottom-inbox" role="tab" aria-controls="bottom-inbox" aria-selected="true">
                                        <i class="icofont icofont-close-squared"></i> Notificações Pendentes
                                    </a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link nav-border txt-info tab-info" id="bottom-home-encarregado" data-bs-toggle="tab" href="#bottom-encarregado" role="tab" aria-controls="bottom-encarregado" aria-selected="false" tabindex="-1">
                                        <i class="icofont icofont-tick-boxed"></i> Notificações Enviadas
                                    </a>
                                </li>
                            </ul>

                            <!-- Conteúdo das Abas -->
                            <div class="tab-content" id="bottom-tabContent">

                                <!-- Notificações Pendentes -->
                                <div class="tab-pane fade show active" id="bottom-inbox" role="tabpanel" aria-labelledby="bottom-inbox-tab">
                                    <br>
                                    <div class="row">
                                        <div class="box-widget widget-module">
                                            <div class="col-md-12">
                                                <div class="box-widget widget-module">
                                                    <div class="widget-container">
                                                        <div class="widget-block">
                                                            <div class="table-responsive custom-scrollbar">
                                                                <table class="display" id="basic-1">
                                                                    <thead>
                                                                        <tr>
                                                                            <th></th>
                                                                            <th>Contacto</th>
                                                                            <th>Conteúdo</th>
                                                                            <th>Data Envio</th>
                                                                            <th>Tipo</th>
                                                                            <th>Estado</th>
                                                                            <th>Data R.</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach ($notificacoesPendentes as $notificacoesPendente)
                                                                            <tr>
                                                                                <td></td>
                                                                                <td>{{ $notificacoesPendente->contacto }}</td>
                                                                                <td>{{ $notificacoesPendente->conteudo }}</td>
                                                                                <td>{{ \Carbon\Carbon::parse($notificacoesPendente->data_envio)->format('d-M-Y') }}</td>
                                                                                <td>{{ $notificacoesPendente->tipo }}</td>
                                                                                <td>{{ $notificacoesPendente->estado }}</td>
                                                                                <td>{{ \Carbon\Carbon::parse($notificacoesPendente->created_at)->format('d-M-Y') }}</td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div> <!-- Fim da tabela -->
                                                        </div> <!-- Fim do widget-block -->
                                                    </div> <!-- Fim do widget-container -->
                                                </div> <!-- Fim do widget-module -->
                                            </div> <!-- Fim do col-md-12 -->
                                        </div> <!-- Fim do box-widget -->
                                    </div> <!-- Fim da row -->
                                </div>
                                <!-- Fim Notificações Pendentes -->

                                <!-- Notificações Enviadas -->
                                <div class="tab-pane fade" id="bottom-encarregado" role="tabpanel" aria-labelledby="bottom-encarregado">
                                    <br>
                                    <div class="row">
                                        <div class="box-widget widget-module">
                                            <div class="col-md-12">
                                                <div class="box-widget widget-module">
                                                    <div class="widget-container">
                                                        <div class="widget-block">
                                                            <div class="table-responsive custom-scrollbar">
                                                                <table class="display" id="basic-9">
                                                                    <thead>
                                                                        <tr>
                                                                            <th></th>
                                                                            <th>Contacto</th>
                                                                            <th>Conteúdo</th>
                                                                            <th>Data Envio</th>
                                                                            <th>Hora</th>
                                                                            <th>Estado</th>
                                                                            <th>Data R.</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach ($notificacoesEnviadas as $notificacoesEnviada)
                                                                            <tr>
                                                                                <td></td>
                                                                                <td>{{ $notificacoesEnviada->contacto }}</td>
                                                                                <td title="{{ $notificacoesEnviada->conteudo }}">
                                                                                    {{ \Illuminate\Support\Str::limit($notificacoesEnviada->conteudo, 18) }}
                                                                                </td>
                                                                                <td>{{ \Carbon\Carbon::parse($notificacoesEnviada->data_envio)->format('d-M-Y') }}</td>
                                                                                <td>{{ \Carbon\Carbon::parse($notificacoesEnviada->updated_at)->format('H:i') }}</td>
                                                                                <td>{{ $notificacoesEnviada->estado }}</td>
                                                                                <td>{{ \Carbon\Carbon::parse($notificacoesEnviada->created_at)->format('d-M-Y') }}</td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div> <!-- Fim da tabela -->
                                                        </div> <!-- Fim do widget-block -->
                                                    </div> <!-- Fim do widget-container -->
                                                </div> <!-- Fim do widget-module -->
                                            </div> <!-- Fim do col-md-12 -->
                                        </div> <!-- Fim do box-widget -->
                                    </div> <!-- Fim da row -->
                                </div>
                                <!-- Fim Notificações Enviadas -->

                            </div> <!-- Fim tab-content -->
                        </div> <!-- Fim card-body -->
                    </div> <!-- Fim card -->
                </div> <!-- Fim col-xxl-12 -->

            </div> <!-- Fim card-body -->
        </div> <!-- Fim container-fluid -->
    </form>
</div>


@endsection

@push('js')


@endpush
