
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

  <input type="hidden" class="form-control" name="aluno" value="{{$aluno->id}}" >
  <input type="hidden" class="form-control" id="value" name="value" >
  <input type="hidden" class="form-control" id="discount" name="discount"  >

  <div class="container-fluid">
     <div class="card-body">
        <div class="card-body dark-alert">
            <div class="alert alert-light-primary" role="alert">
            <div class="row">
              <div class="col-md-4">
                  
                  <h4>Nome: <a href="{{route('aluno.show',['codigo'=>$aluno->codigo])}}"> {{$aluno->pessoa->nome}} </a> </h4>
                  <h4>Code: <a href="{{route('aluno.show',['codigo'=>$aluno->codigo])}}">{{$aluno->codigo}} </a></h4>
                  <h4>Doc.: {{$aluno->pessoa->tipo_documento}} - {{$aluno->pessoa->numero_documento}}</h4>

              </div>
              <div class="col-md-4">

                  <h4>Forma Pagamento</h4> 
                  <h4>Banco: BIM <span id="total" hidden>0,00</span></h4> 
                  <h4>Entidade: 90028 <span id="desconto" hidden>0,00</span> </h4>

              </div>
              <div class="col-md-4" data-bs-toggle="modal" data-bs-target=".bd-example-modal-lg">
                    <br>
                    <a class="btn btn-outline-warning ms-2" target="_blank" href="{{route('propina.factura',['alunoCodigo'=>$aluno->codigo])}}" >
                        Gerar Referencias
                    </a>	
              </div>
          </div>
            </div>
        </div>


<div class="col-xxl-12" style="margin-top: 0.5%"> 
    <div class="card title-line">
        <div class="card-body"> 
        <ul class="nav nav-tabs border-tab mb-0" id="bottom-tab" role="tablist">
            <li class="nav-item" role="presentation"><a class="nav-link nav-border txt-info tab-info active" id="bottom-inbox-tab" data-bs-toggle="tab" href="#bottom-inbox" role="tab" aria-controls="bottom-inbox" aria-selected="true"><i class="icofont icofont-close-squared"></i>Mensalidades Pendente</a></li>
            <li class="nav-item" role="presentation"><a class="nav-link nav-border txt-info tab-info" id="bottom-home-encarregado" data-bs-toggle="tab" href="#bottom-encarregado" role="tab" aria-controls="bottom-encarregado" aria-selected="false" tabindex="-1"><i class="icofont icofont-tick-boxed"> </i>Mensalidades Pagas</a></li>
        </ul>
        <!-- Inscricoes -->
        <div class="tab-content" id="bottom-tabContent">

          <!-- Mensalidades Pendentes -->
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
                                                        <th>Propina</th>
                                                        <th>Prazo</th>
                                                        <th>Curricular</th>
                                                        <th>Co-Curr</th>
                                                        <th>Multa</th>
                                                        <th>Total</th>
                                                        <th>Referência</th>
                                                        <th>Acção</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                    @foreach ($mensalidades as $mensalidade)
                                                      
                                                        <tr>
                                                            <td>
                                                                @if($mensalidade->pagamento_id!=null)
                                                                    
                                                                @else
                                                                    <input type="checkbox" class="form-check-input" name="meses[]" value="{{$mensalidade->id}}-{{($mensalidade->valor+$mensalidade->valor_co_curricular)-$mensalidade->desconto+($mensalidade->multa/100*($mensalidade->valor+$mensalidade->valor_co_curricular)-$mensalidade->desconto)}}-{{$mensalidade->mes->numero}}" > 
                                                                @endif
                                                            </td>
                                                            <td>{{$mensalidade->mes->nome}} {{\Carbon\Carbon::parse($mensalidade->data_inicio)->format('Y')}}</td>
                                                            <td>{{\Carbon\Carbon::parse($mensalidade->data_fim)->format('d-M-Y')}}</td>
                                                            <td>{{number_format($mensalidade->valor, 2, ',', '.') }}</td>
                                                            <td>{{number_format($mensalidade->valor_co_curricular, 2, ',', '.')}}</td>
                                                            <td>{{number_format($mensalidade->multa, 0, ',', '.') }}%</td>
                                                            <td>
                                                                {{number_format(($mensalidade->valor+$mensalidade->valor_co_curricular)-$mensalidade->desconto+($mensalidade->multa/100*($mensalidade->valor+$mensalidade->valor_co_curricular)), 2, ',', '.') }}
                                                            </td>
                                                            <td>
                                                                 {{$mensalidade->pagamento->referencia}}   
                                                            </td>
                                                            <td>
                                                              <a href="{{route('pagarPropina.index',['codigo'=>$mensalidade->id])}}" class="btn btn-danger btn-xs" href="#!">Pagar</a> 
                                                              <a href="{{route('corrigirPropina.index',['codigo'=>$mensalidade->id])}}" class="btn btn-warning btn-xs" href="#!">Corrigir</a> 
                                                              {{-- <a href="{{route('pagarPropinaMpesa.index',['codigo'=>$mensalidade->id])}}" class="btn btn-danger btn-xs" href="#!">Pagar Mpesa</a> --}} 
                                                              <a class="btn btn-info btn-xs btn-ref-sms"
                                                                href="javascript:void(0)"
                                                                data-codigo="{{ $aluno->codigo }}"
                                                                data-mensalidade="{{ $mensalidade->id }}"
                                                                data-mes="{{ $mensalidade->mes->nome }}"
                                                                data-ano="{{ \Carbon\Carbon::parse($mensalidade->data_inicio)->format('Y') }}">
                                                                    Ref. SMS
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
                        </div>
                    </div>
                </div>
          </div>
          <!-- Fim Mensalidades Pendentes -->

          <!-- Mensalidades Pagas -->
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
                                                        <th>Propina</th>
                                                        <th>Curr.</th>
                                                        <th>Co-Cur.</th>
                                                        <th>Multa</th>
                                                        <th>Total</th>
                                                        <th>Data-Pag.</th>
                                                        <th>Recibo</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                    @foreach ($mensalidadesPago as $mensalidade)
                                                      
                                                        <tr>
                                                            <td>

                                                            </td>
                                                            <td>{{$mensalidade->mes->nome}} {{\Carbon\Carbon::parse($mensalidade->data_inicio)->format('Y')}}</td>
                                                            <td>{{number_format($mensalidade->valor, 2, ',', '.') }}</td>
                                                            <td>{{number_format($mensalidade->valor_co_curricular, 2, ',', '.')}}</td>
                                                            <td>{{number_format($mensalidade->multa, 0, ',', '.') }}%</td>
                                                            <td>
                                                                {{number_format(($mensalidade->valor+$mensalidade->valor_co_curricular)-$mensalidade->desconto+($mensalidade->multa/100*($mensalidade->valor+$mensalidade->valor_co_curricular)), 2, ',', '.') }}
                                                            </td>
                                                            <td>{{\Carbon\Carbon::parse($mensalidade->data_fim)->format('d-M-Y')}}</td>
                                                            <td>
                                                              <a target="_blank" href="{{route('propinaRecibo.recibo',['referencia'=>$mensalidade->pagamento->referencia])}}" class="btn btn-success btn-xs" href="#!">Recibo</a> 
                                                               @if(Auth::user()->telefone==844870386)
                                                                <a href="#"  class="btn btn-warning btn-xs deactivate-btn" data-nome="{{ $mensalidade->pagamento->referencia }}">Canc.</a> 
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
                        </div>
                    </div>
                </div>
            </div>
         <!-- Mensalidades Pagas -->


        </div>
    </div>
</div>


        <br>  

        <br><br>
     </div>  
  </div>

  <div class="modal fade bd-example-modal-lg1" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModal" aria-hidden="true">
    <div class="modal-dialog modal-lg">
    <div class="modal-content"> 
        <div class="modal-header">
            <h4 class="modal-title" id="myExtraLargeModal">Gerar Factura </h4>
            <button class="btn-close py-0" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body dark-modal">


            <div class="form-group">
                <center>
                    <h3>Subtotal: <span id="total3">0,00</span></h3>
                    <h3>Desconto: <span id="desconto3">0,00</span></h3>
                    <h3>Total: <span id="meses3">0,00</span></h3>
                </center>
            </div>

        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
            <button class="btn btn-primary" id="pagar-bim" type="submit" >
                <span id="botao_texto">{{__('Confirmar Factura')}}</span>
                <i id="icon_enviar" class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
            </button>
        </div>
    </div>
    </div>
</div>


</form>

</div>


@endsection

@push('js')


<script>

// Obtém todos os checkboxes com a classe 'item'
const checkboxes = document.querySelectorAll('.form-check-input');

// Adiciona um evento de clique a cada checkbox
checkboxes.forEach(function(checkbox) {
    checkbox.addEventListener('click', function() {
        // Chama a função para recalcular o total
        calcularTotal();
    });
});


function calcularTotal() {

    let subtotal = 0;
    let desconto = 0;
    let meses = 0;
    let total = 0;
    let total1 = 0;

    // Cria um objeto Date com a data atual
    const dataAtual = new Date();
    // Obtém o mês atual (formato de 0 a 11)
    const mesAtualZeroIndex = dataAtual.getMonth();
    // Adiciona 1 ao mês atual para obter o formato de 1 a 12
    const mesAtual = mesAtualZeroIndex + 1;

    // Percorre todos os checkboxes e verifica se estão marcados
    checkboxes.forEach(function(checkbox) {
        if (checkbox.checked) {
            // Se estiverem marcados, adiciona o valor do checkbox ao total
            let partes = checkbox.value.split('-');
            let valor = partes[1];
            let mes = partes[2];

            valorFloat = parseFloat(valor);
            total += valorFloat ;

            if(mes > mesAtual){

                subtotal = subtotal + valorFloat-7/100*valorFloat; 
                desconto += 7/100*valorFloat; 
                  
            }else{
                subtotal = subtotal + valorFloat;   
            }

            meses+= 1; 
        }
    });

    
    // Atualiza o valor total na página
    document.getElementById('total').textContent = total.toFixed(2);
    document.getElementById('desconto').textContent = desconto.toFixed(2);
    document.getElementById('meses').textContent = (total-desconto).toFixed(2);

    document.getElementById('total3').textContent = total.toFixed(2) + " MT";
    document.getElementById('desconto3').textContent = desconto.toFixed(2) + " MT";
    document.getElementById('meses3').textContent = (total - desconto).toFixed(2) + " MT";

    document.getElementById('value').value = (total-desconto).toFixed(2);
    document.getElementById('discount').value = desconto.toFixed(2);
}


    $(document).ready(function () {
        $('#form-pagamento').on('submit', function (e) {
            e.preventDefault(); // Impede o envio padrão do formulário

            var formAction = '';// Obtém a URL do formulário
            var formData = $(this).serialize(); // Serializa os dados do formulário

            // Determina o método de pagamento com base no botão clicado
            if ($('#pagar-cartao').is(':focus')) {
                formAction = '';
            } else if ($('#pagar-cash').is(':focus')) {
                formAction = '';
            } else if ($('#pagar-bim').is(':focus')) {
                formAction = '{{ route("propina.store") }}';
            }

            // Envia a requisição AJAX
            $.ajax({
                url: formAction,
                method: 'POST',
                data: formData,
                success: function (response) {
                    // Manipular a resposta do servidor

                    Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: response.message,
                      });

                    var host = window.location.origin+"/propina/factura/"+response.factura;

                    window.open(host, "_blank");

                    window.location.reload();

                },
                error: function (xhr, status, error) {
 
                    // Exibe a mensagem de erro
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: errors.responseJSON.message,
                    });
                    
                }
            });
        });
    });

</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const deactivateButtons = document.querySelectorAll('.deactivate-btn');

    deactivateButtons.forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault();

            const pagamentoReferencia = this.getAttribute('data-nome');

            if (!pagamentoReferencia) {
                console.error('Referência ausente.');
                return;
            }

            Swal.fire({
                title: 'Tem certeza?',
                text: `Você está prestes a cancelar o Pagamento ${pagamentoReferencia}. Esta ação é irreversível!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, cancelar!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    cancelarFatura(pagamentoReferencia);
                }
            });
        });
    });

    function cancelarFatura(referencia) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        
        if (!csrfToken) {
            console.error('CSRF token não encontrado.');
            Swal.fire('Erro!', 'Token CSRF ausente.', 'error');
            return;
        }

        fetch(`/propina/cancelar/pagamento/${referencia}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire(
                    'Cancelado!',
                    data.message,
                    'success'
                ).then(() => location.reload());
            } else {
                Swal.fire('Erro!', data.message || 'Erro ao cancelar.', 'error');
            }
        })
        .catch(error => {
            console.error(error);
            Swal.fire('Erro!', 'Problema de rede ao cancelar.', 'error');
        });
    }
});
</script>

<script>
document.querySelectorAll('.btn-ref-sms').forEach(btn => {

    btn.addEventListener('click', function () {

        const codigo = this.dataset.codigo;
        const mensalidadeId = this.dataset.mensalidade;
        const mes = this.dataset.mes;
        const ano = this.dataset.ano;

        Swal.fire({
            title: 'Enviar referência?',
            html: `
                <strong>Mês:</strong> ${mes} ${ano}<br>
                Esta ação irá enviar a referência de pagamento da mensalidade
                para o encarregado via SMS.
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim, enviar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {

            if (result.isConfirmed) {

                Swal.fire({
                    title: 'Aguarde...',
                    text: 'A enviar SMS...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                fetch(`/mensalidade/sms/${codigo}/${mensalidadeId}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(response => {

                    Swal.close();

                    Swal.fire({
                        icon: response.status ? '1' : '0',
                        title: response.status ? 'Sucesso' : 'Erro',
                        text: response.message
                    });

                    window.location.reload();

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


@endpush
