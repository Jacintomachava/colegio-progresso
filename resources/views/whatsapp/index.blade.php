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
                     Notificações WhatsApp
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
                            <th>Telefone</th>
                            <th>Mensagem</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Data</th>
                            <th>Hora</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach($reclamacoes as $reclamacao)
                            <tr>
                                <td></td>
                                <td>{{$reclamacao->telefone}}</td>
                                <td data-container="body" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="{{$reclamacao->mensagem}}">
                                    {{ \Illuminate\Support\Str::limit($reclamacao->mensagem, 40) }}
                                </td>
                                
                                <td>{{$reclamacao->tipo}}</td>
                                <td>

                                    @if($reclamacao->estado=='Pendente')
                                        @if($reclamacao->tipo=='Reclamação' || $reclamacao->tipo=='Pedido' || $reclamacao->tipo=='Outro')
                                            <!-- Botão no foreach (mantenha exatamente como está) -->
                                            <a href="#" class="btn btn-warning btn-xs abrir-modal-resposta" 
                                                data-bs-toggle="modal" 
                                                data-bs-target=".bd-example-modal-lg"
                                                data-id="{{ $reclamacao->id }}"
                                                data-mensagem="{{ $reclamacao->mensagem }}">
                                               Responder
                                            </a>
                                        @endif
                                    @else
                                        {{$reclamacao->estado}}
                                    @endif


                                </td>
                                <td>{{ \Carbon\Carbon::parse($reclamacao->created_at)->format('d-M-Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($reclamacao->created_at)->format('H:i') }}</td>
                            </tr>

                        @endforeach

                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>


<!-- Modal (ajustado para corresponder ao target do botão) -->
<div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="formResposta">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Responder</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <p><strong>Reclamação/Pedido:</strong><br><i id="mensagemTexto"></i></p>
          <div class="mb-3">
            <label class="form-label">Resposta</label>
            <textarea class="form-control" name="resposta" rows="4" required></textarea>
          </div>
          <!-- Campo corrigido - type hidden e com ID único -->
          <input type="hidden" name="reclamacao_id" id="reclamacaoIdHidden" value="">
          
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary" id="btnSubmit">
            <span id="btnText">Enviar Resposta</span>
            <i id="btnIcon" class="ri-arrow-right-line"></i>
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

    // Evento quando o modal é mostrado - CORREÇÃO PRINCIPAL AQUI
    $('.bd-example-modal-lg').on('show.bs.modal', function(event) {
        const button = $(event.relatedTarget);
        const id = button.data('id');
        const mensagem = button.data('mensagem');
        
        // DEBUG - Verifique no console do navegador
        console.log('ID capturado:', id);
        console.log('Mensagem capturada:', mensagem);
        
        // Preenche os dados no modal - FORMA CORRETA
        $('#mensagemTexto').text(mensagem);
        $('#reclamacaoIdHidden').val(id);  // Usando o ID correto do campo
        
        // DEBUG - Verifique se o valor foi atribuído
        console.log('Valor atribuído ao campo hidden:', $('#reclamacaoIdHidden').val());
    });

    // Validação e submissão do formulário
    $("#formResposta").validate({
        rules: {
            resposta: {
                required: true,
                minlength: 5
            }
        },
        submitHandler: function(form) {
            // Obter o ID diretamente do campo hidden
            const reclamacaoId = $('#reclamacaoIdHidden').val();
            
            // DEBUG - Verifique o ID antes do envio
            console.log('ID a ser enviado:', reclamacaoId);
            
            if (!reclamacaoId) {
                Swal.fire('Erro', 'ID da reclamação não encontrado', 'error');
                return false;
            }

            const $btn = $('#btnSubmit');
            $btn.prop('disabled', true).find('#btnText').text('Enviando...');

            $.ajax({
                type: "POST",
                url: "{{ route('reclamacao.responder') }}",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    resposta: $('textarea[name="resposta"]').val(),
                    reclamacao_id: reclamacaoId  // Enviando o ID capturado
                },
                success: function(response) {
                    if (response.status === 1) {
                        Swal.fire('Sucesso!', response.message, 'success')
                           .then(() => location.reload());
                    } else {
                        Swal.fire('Erro!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Erro!', xhr.responseJSON?.message || 'Erro no servidor', 'error');
                },
                complete: function() {
                    $btn.prop('disabled', false).find('#btnText').text('Enviar Resposta');
                }
            });
        }
    });
});
</script>


@endpush
