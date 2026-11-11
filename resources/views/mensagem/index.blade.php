@extends('layouts.app')

@push('css')

@endpush

@section('conteudo')

<div class="row alert alert-light-primary" role="alert" >
  <div class="col-sm-6" > 
    <p style="font-size: 7pt;">SMS cada envio de uma SMS vale 1.85 creditos <i style="font-size: 7pt;">(uma SMS normal)</i></p>
    <p style="font-size: 7pt;">Cada Mensagem de WhatsApp vala a 0.02 creditos <i style="font-size: 7pt;">(resposta da conversa iniciado pelo utilizador)</i></p>
  </div>  
  <div class="col-sm-3" >
    Crédito Disponivel {{$saldo->saldo}}
  </div>
  <div class="col-sm-3" >
    SMS Pendentes {{$creditoSMSPendente}} Crédito
  </div>
</div>

<div class="row">
    <div class="col-md-6"></div>
    <div class="col-md-6">
      <a href="{{route('mensagem.create')}}">
        <button class="btn btn-pill btn-primary btn-sm" >Enviar SMS</button>
      </a>
      <a href="{{route('SMSperidica.create')}}">
            <button class="btn btn-pill btn-warning btn-sm" >Mensagem Periodicas</button>
      </a>
      <a href="#">
            <button class="btn btn-pill btn-success btn-sm" data-bs-toggle="modal" data-bs-target=".bd-example-modal-lg">Comprar Crédito</button>
      </a>
      <a href="{{route('notificacao.saldo')}}">
            <button class="btn btn-pill btn-primary btn-sm" >Notificacao de Saldo</button>
      </a>
    </div>
</div>

<div class="col-xxl-12" > 
    <div class="card title-line">
        <div class="card-body"> 
        <ul class="nav nav-tabs border-tab mb-0" id="bottom-tab" role="tablist">
            <li class="nav-item" role="presentation"><a class="nav-link nav-border txt-info tab-info active" id="bottom-inbox-tab" data-bs-toggle="tab" href="#bottom-inbox" role="tab" aria-controls="bottom-inbox" aria-selected="true"><i class="icofont icofont-ui-message"></i>Mensagens</a></li>
            <li class="nav-item" role="presentation"><a class="nav-link nav-border txt-info tab-info" id="bottom-home-encarregado" data-bs-toggle="tab" href="#bottom-encarregado" role="tab" aria-controls="bottom-encarregado" aria-selected="false" tabindex="-1"><i class="icofont icofont-ui-call"> </i>Contactos</a></li>
            <li class="nav-item" role="presentation"><a class="nav-link nav-border txt-info tab-info" id="bottom-contact-tab" data-bs-toggle="tab" href="#bottom-contact" role="tab" aria-controls="bottom-contact" aria-selected="false" tabindex="-1"><i class="icofont icofont-pie-chart"></i>Uso Credito</a></li>
            <li class="nav-item" role="presentation"><a class="nav-link nav-border txt-info tab-info" id="bottom-home-tab" data-bs-toggle="tab" href="#bottom-home" role="tab" aria-controls="bottom-home" aria-selected="false" tabindex="-1"><i class="icofont icofont-coins"> </i>Credito Comprado</a></li>
            <li class="nav-item" role="presentation"><a class="nav-link nav-border txt-info tab-info" id="bottom-home-tab-club1" data-bs-toggle="tab" href="#bottom-register1" role="tab" aria-controls="bottom-home1" aria-selected="false" tabindex="-1" hidden><i class="icofont icofont-money-bag"> </i>Lucro</a></li>
            <li class="nav-item" role="presentation"><a class="nav-link nav-border txt-info tab-info" id="bottom-home-tab-club" data-bs-toggle="tab" href="#bottom-register" role="tab" aria-controls="bottom-home" aria-selected="false" tabindex="-1"><i class="icofont icofont-ui-message"> </i>Mensagem Periodica</a></li>
        </ul>
        <!-- Inscricoes -->
        <div class="tab-content" id="bottom-tabContent">
          <div class="tab-pane fade" id="bottom-register1" role="tabpanel" aria-labelledby="bottom-home-tab">
              
              <br>
              <div id="grafico2" style="width: 600px; height: 400px;"></div>

          </div> 
          
          <div class="tab-pane fade" id="bottom-register" role="tabpanel" aria-labelledby="bottom-home-tab">
              
                <br>
                <div class="col-sm-12"> 
                  <div class="card title-line">
                    <div class="card-block row">
                      <div class="col-sm-12 col-lg-12 col-xl-12">
                        <div class="table-responsive custom-scrollbar">
                          <table class="table">
                            <thead class="table-dark">
                              <tr>
                                <th scope="col">#</th>
                                <th scope="col">Titulo</th>
                                <th scope="col">SMS</th>
                                <th scope="col">Estado</th>
                                <th scope="col">Data Envio</th>
                                <th scope="col">Destinatario</th>
                                <th scope="col">Acção</th>
                              </tr>
                            </thead>
                            <tbody>

                            @foreach($mensagensPeriodicas as $mensagensPeriodica)
                                <tr>
                                    <td>{{ $loop->iteration }}</td> <!-- Número sequencial -->
                                    <td>{{ $mensagensPeriodica->titulo }}</td>
                                    <td data-container="body" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="{{$mensagensPeriodica->descricao}}">
                                      <i>{{ Str::limit($mensagensPeriodica->descricao, 10) }}</i>
                                    </td>
                                    <td>@if($mensagensPeriodica->estado) Activo @else Inactivo @endif</td>
                                    <td>Dia {{$mensagensPeriodica->dia_do_mes}} de cada mes</td>
                                    <td>{{$mensagensPeriodica->destinatario}} </td>
                                    <td>
                                      <form action="{{ route('mensagens.toggle', $mensagensPeriodica->id) }}" method="POST">
                                          @csrf
                                          @method('PUT')

                                          @if($mensagensPeriodica->estado)
                                              <button class="btn btn-danger btn-xs">Inativar</button>
                                          @else
                                              <button class="btn btn-success btn-xs">Activar</button>
                                          @endif
                                      </form>
                                      
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
          
            <!-- Encarregados -->
            <div class="tab-pane fade" id="bottom-encarregado" role="tabpanel" aria-labelledby="bottom-encarregado">

                <br>
                <div class="col-sm-12"> 
                  <div class="card title-line">
                    <div class="card-block row">
                      <div class="col-sm-12 col-lg-12 col-xl-12">
                        <div class="table-responsive custom-scrollbar">
                          <table class="table">
                            <thead class="table-dark">
                              <tr>
                                <th scope="col">#</th>
                                <th scope="col">Telefone</th>
                                <th scope="col">N° Conversas</th>
                                <th scope="col">Data</th>
                                <th scope="col">Hora</th>
                                <th scope="col">Acção</th>
                              </tr>
                            </thead>
                            <tbody>

                            @foreach($contatos as $contato)
                                <tr>
                                    <td>{{ $loop->iteration }}</td> <!-- Número sequencial -->
                                    <td>{{ $contato->telefone }}</td>
                                    <td>{{ $contato->conversas }}</td>
                                    <td>{{ \Carbon\Carbon::parse($contato->updated_at)->format('d-M-Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($contato->updated_at)->format('H:i') }}</td>
                                    <td>
                                      @if(filled($contato->telefone))
                                          <a class="btn btn-secondary btn-xs activate-btn"
                                            href="{{ route('mensagem.show', [
                                                'contacto' => $contato->telefone
                                            ]) }}">
                                              Ver Conversas
                                          </a>
                                      @else
                                          <span class="text-danger">Contacto inválido</span>
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
            <!-- Fim Encarregados -->  
        </div>
        <!-- Fim Inscricoes -->

        
        <div class="tab-content" id="bottom-tabContent">

            <!-- Contactos Urgentes -->
            <div class="tab-pane fade" id="bottom-home" role="tabpanel" aria-labelledby="bottom-home-tab">

                <br>
                <div class="col-sm-12"> 
                  <div class="card title-line">
                    <div class="card-block row">
                      <div class="col-sm-12 col-lg-12 col-xl-12">
                        <div class="table-responsive custom-scrollbar">
                          <table class="table">
                            <thead class="table-dark">
                              <tr>
                                <th scope="col">#</th>
                                <th scope="col">Pacote</th>
                                <th scope="col">Número Crédito</th>
                                <th scope="col">Preço por Crédito</th>
                                <th scope="col">Valor Total</th>
                                <th scope="col">Data</th>
                              </tr>
                            </thead>
                            <tbody>

                            @foreach($pacotes as $pacote)
                                <tr>
                                    <td>{{ $pacote->factura }}</td> <!-- Número sequencial -->
                                    <td>{{ $pacote->tipo_pacote }}</td>
                                    <td>{{ $pacote->numero_credito }}</td>
                                    <td>{{ $pacote->preco_por_credito }}MZN/Crédito</td>
                                    <td>{{ $pacote->valor }}MZN</td>
                                    <td>{{ \Carbon\Carbon::parse($pacote->updated_at)->format('d-M-Y') }}</td>
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
            <!-- Fim Contactos Urgentes -->


             <!-- Matriculas -->
            <div class="tab-pane fade show active" id="bottom-inbox" role="tabpanel" aria-labelledby="bottom-inbox-tab">

              <br>
              <div class="col-sm-12"> 
                <div class="card title-line">
                  <div class="card-block row">
                    <div class="col-sm-12 col-lg-12 col-xl-12">
                      <div class="table-responsive custom-scrollbar">
                        <table class="table">
                          <thead class="table-dark">
                            <tr>
                              <th scope="col">Contactos</th>
                              <th scope="col">Canal</th>
                              <th scope="col">Tipo</th>
                              <th scope="col">conteudo</th>
                              <th scope="col">Qtd</th>
                              <th scope="col">Credito</th>
                              <th scope="col">Data</th>
                              <th scope="col">Hora</th>
                            </tr>
                          </thead>
                          <tbody>

                          @foreach($mensagens as $mensagem)
                            <tr>
                              <td>{{$mensagem->telefone}}</td>
                              <td>{{$mensagem->canal}}</td>
                              <td>
                                {{$mensagem->tipo}}
                              </td>
                              <td data-container="body" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="{{$mensagem->descricao}}">
                                {{ Str::limit($mensagem->descricao, 10) }}
                              </td>
                              <td>{{$mensagem->qtd}}</td>
                              <td>{{$mensagem->credito}}</td>
                              <td>
                                {{ \Carbon\Carbon::parse($mensagem->created_at)->format('d-M-Y') }}
                              </td>
                              <td>
                                {{ \Carbon\Carbon::parse($mensagem->created_at)->format('H:s') }}
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
            <!-- Fim Matriculas -->

            <!-- Recolher Crianca -->
            <div class="tab-pane fade" id="bottom-contact" role="tabpanel" aria-labelledby="bottom-contact-tab">
                 <br>
                 <div class="col-sm-12"> 
                  <div id="consumo-chart" style="width: 600px; height: 400px;"></div>
                 </div>
        
            </div>
            <!-- Fim Recolher Crianca -->
            
        </div>
        </div>
    </div>
</div>


<div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModal" aria-hidden="true">
    <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form  id="form">  
        <div class="modal-header">
            <h4 class="modal-title" id="myExtraLargeModal">Compra de Crédito</h4>
            <button class="btn-close py-0" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body dark-modal">

            @csrf

            <div class="row">
                

                <div class="col-6">
                    <label class="form-label" for="passwordwizard">Digite Valor<span class="txt-danger">*</span></label>
                    <input class="form-control" type="text" name="valor" placeholder="valor Mpesa" >
                </div>

                <div class="col-6">
                    <label class="form-label" for="passwordwizard">Telefone Mpesa<span class="txt-danger">*</span></label>
                    <input class="form-control" type="text" name="telefone" placeholder="Numero Mpesa" >
                </div>


            </div>

        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
            <button class="btn btn-primary" id="botao_salvar" type="submit" >
                <span id="botao_texto">{{__('Comprar Pacote')}}</span>
                <i id="icon_enviar" class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
            </button>
        </div>
      </form>  
    </div>
    </div>
</div>


@endsection

@push('js')

  <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>

<script>

$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("#form").validate({
        // Adicionar regras para cada campo
        rules: {
            valor: {
                required: true,
                min: 500,
                max: 10000
            },
            telefone: {
                required: true,
                minlength: 9,
                maxlength: 9,
            }
        },
        submitHandler: function(form) {
            $.ajax({
                type: "POST",
                url: "{{route('mensagem.storeCompraSMS')}}",
                data: $(form).serialize(), // Corrigido para usar `form` em vez de `this`

                beforeSend: function () {
                    // Desabilita o botão de envio e altera o ícone para mostrar que está autenticando
                    $('#botao_salvar').attr('disabled', true);
                    $('#icon_enviar').removeClass('ri-arrow-right-line').addClass('spinner-border ri-loader-2-line');
                    $('#botao_texto').text('Comprando Credito...');
                },

                success: function(response) {
                    // Habilita o botão e retorna o ícone original
                    $('#botao_salvar').attr('disabled', false);
                    $('#icon_enviar').removeClass('spinner-border ri-loader-2-line').addClass('ri-arrow-right-line');
                    $('#botao_texto').text('Comprar Credito');

                    // Redireciona ou exibe uma mensagem de erro com base na resposta
                    if(response.status == 1) {

                      Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: response.message,
                      });

                      window.location.reload();

                    } else if(response.status == 0) {

                      Swal.fire({
                            icon: 'error',
                            title: 'Erro!',
                            text: response.message,
                      });

                    }
                },
                error: function(errors) {
                    // Habilita o botão e retorna o ícone original em caso de erro
                    $('#botao_salvar').attr('disabled', false);
                    $('#icon_enviar').removeClass('spinner-border ri-loader-2-line').addClass('ri-arrow-right-line');
                    $('#botao_texto').text('Comprar Credito');

                    // Exibe a mensagem de erro
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: errors.responseJSON.message,
                    });

                }
            });
        }
    });
});

</script>

  <script>
      // 🟢 Dados vindos do Laravel
      const meses = @json($meses);
      const consumoPorCanal = @json($consumoPorCanal);

      // 🟢 Estruturar os dados para o gráfico
      const consumoWhatsApp = [];
      const consumoSMS = [];

      meses.forEach(mes => {
          const consumoWhatsAppMes = consumoPorCanal.find(item => item.mes === mes && item.canal === 'WhatsApp') || { total_credito: 0 };
          const consumoSMSMes = consumoPorCanal.find(item => item.mes === mes && item.canal === 'SMS') || { total_credito: 0 };

          consumoWhatsApp.push(consumoWhatsAppMes.total_credito);
          consumoSMS.push(consumoSMSMes.total_credito);
      });

      // 🟢 Inicializar o gráfico
      const chartConsumo = echarts.init(document.getElementById('consumo-chart'));

      // 🟢 Configuração do gráfico
      chartConsumo.setOption({
          title: {
              text: 'Consumo de Créditos por Mês e Canal',
              left: 'center',
          },
          tooltip: {
              trigger: 'axis',
              axisPointer: { type: 'shadow' },
          },
          legend: {
              bottom: '0%',
              data: ['WhatsApp', 'SMS'],
          },
          xAxis: {
              type: 'category',
              data: meses,
          },
          yAxis: {
              type: 'value',
              name: 'Total de Créditos',
          },
          series: [
              {
                  name: 'WhatsApp',
                  type: 'bar',
                  data: consumoWhatsApp,
                  itemStyle: { color: '#25D366' }, // Verde WhatsApp
              },
              {
                  name: 'SMS',
                  type: 'bar',
                  data: consumoSMS,
                  itemStyle: { color: '#FF9900' }, // Laranja SMS
              },
          ],
      });
  </script>

  <script>

        // 🟢 Gráfico 2: Consumo de Créditos e Lucro por Mês
        var chart2 = echarts.init(document.getElementById('grafico2'));
        var meses2 = @json($consumoLucroPorMes->pluck('mes'));
        var creditoMes = @json($consumoLucroPorMes->pluck('total_credito'));
        var lucroMes = @json($consumoLucroPorMes->pluck('lucro'));

        var option2 = {
            title: { text: '' },
            tooltip: { trigger: 'axis' },
            legend: { data: ['Créditos Consumidos', 'Lucro'] },
            xAxis: { type: 'category', data: meses2 },
            yAxis: { type: 'value' },
            series: [
                { name: 'Créditos Consumidos', type: 'bar', data: creditoMes },
                { name: 'Lucro', type: 'bar', data: lucroMes }
            ]
        };
        chart2.setOption(option2);
    </script>

@endpush
