@extends('layouts.app')

@push('css')

@endpush

@section('conteudo')

<div class="col-sm-12">
    <div class="card title-line">
        <div class="card-header bg-dark">
          <h5 class="text-white">{{$aluno->pessoa->nome}}</h5>
          <div class="card-header-right dark-color-header">
              <h5 class="text-white">
                {{$aluno->codigo}} 
              </h5>
          </div>
        </div>
        <div class="card-body bg-dark">
        <div class="d-flex align-items-center gap-3 pills-blogger"> 
            <div class="blog-wrapper col-md-3"> 
               <div class="customers d-inline-block avatar-group">
                <ul>
                <li class="d-inline-block">
                  @if($aluno->escola->logotipo=='avatar.png')
                    <img class="img-100 b-r-8" src="{{ URL('/logotipo/'. session('logotipo') ) }}" alt="user profile">
                  @else
                     
                    <img class="img-100 b-r-8" src="{{ URL('/logotipo/'.$aluno->escola->logotipo) }}" alt="user profile">
                  @endif
                </li>
                <li class="d-inline-block">
                  @if($aluno->avatar=='avatar.png')
                    <img class="img-80 b-r-30" src="{{ URL('/avatar/avatar.png') }}" alt="user profile">
                  @else
                   <img class="img-80 b-r-30" src="{{ URL('/avatar/'.$aluno->avatar) }}" alt="user profile">
                  @endif
                </li>
                </ul>
              </div>
            </div>

            <div class="col-md-4"> 
              <b>Nome de Aluno:</b><span style="padding-left: 3%"> {{$aluno->pessoa->nome}}</span><br>
              <b>Tipo Documento:</b><span style="padding-left: 3%"> {{$aluno->pessoa->tipo_documento}}</span><br>
              <b>Provincia:</b><span style="padding-left: 3%"> {{$aluno->provincia->nome}}</span><br>
              <b>Classe:</b><span style="padding-left: 3%"> {{$aluno->classe->nome}}</span><br>

              @foreach($irmaos as $irmao)
                <b>---Irmao:</b><span style="padding-left: 3%"> {{$irmao->aluno->codigo}}</span>---<br>
              @endforeach
            </div>

            <div class="col-md-5"> 
              <b>Data Nascimento:</b><span style="padding-left: 3%"> {{ \Carbon\Carbon::parse($aluno->pessoa->data_nascimento)->format('d-M-Y') }}</span><br>
              <b>Numero de Documento:</b><span style="padding-left: 3%">{{$aluno->pessoa->numero_documento}}</span><br>
              <b>Genero:</b><span style="padding-left: 3%">{{$aluno->pessoa->genero}}</span><br>
              <b>Data Registo:</b><span style="padding-left: 3%">{{ \Carbon\Carbon::parse($aluno->created_at)->format('d-M-Y') }}</span><br>
            </div>


            
        </div>
        </div>
        <div class="card-footer bg-dark">
   
            @role('Secretaria')
                  <a class="btn btn-secondary btn-xs activate-btn" href="{{route('aluno.edit',['codigo'=>$aluno->codigo])}}" >
                      Editar Aluno
                  </a>
                  <a class="btn btn-success btn-xs activate-btn" href="{{route('propina.show',['alunoID'=>$aluno->codigo])}}" >
                      Mensalidades
                  </a>
                  @if($matriculaActual->pagou==0)
                    <a class="btn btn-info btn-xs btn-referencia"
                      href="javascript:void(0)"
                      data-codigo="{{ $aluno->codigo }}">
                        Referência Matrícula SMS
                    </a>
                  @endif

                 @if(count($totalMensalidadeActual) == 0)
                    <a class="btn btn-danger btn-xs btn-gerar-referencia"
                      href="javascript:void(0)"
                      data-codigo="{{ $aluno->codigo }}"
                      data-classe="{{ $aluno->classe->nome }}"
                      data-aluno="{{ $aluno->pessoa->nome }}">
                        Gerar Referência Propinas
                    </a>
                @endif

                   <a href="#" 
                      class="btn btn-warning btn-xs mudar-classe" 
                      data-id="{{ $aluno->codigo }}">
                      Mudar de classe
                    </a>

                    <a href="#"
                      class="btn btn-success btn-xs mudar-foto"
                      data-id="{{ $aluno->codigo }}"
                      data-foto="{{ $aluno->avatar }}" hidden>
                      Mudar Foto
                    </a>

                    <a href="#"
                      class="btn btn-primary btn-xs transferir-aluno"
                      data-bs-toggle="modal"
                      data-bs-target="#modalTransferencia"
                      data-aluno="{{ $aluno->id }}"
                      data-nome="{{ $aluno->pessoa->nome }}"
                      data-escola="{{ $aluno->escola_id }}"
                      data-classe="{{ $matriculaActual->classe_id }}"
                      data-turma="{{ $matriculaActual->turma_id }}">
                      <i class="fa fa-exchange-alt"></i> Transferir Aluno
                    </a>
            
            @endrole 
                  
        </div>
    </div>
</div>

<div class="col-xxl-12" style="margin-top: -2%"> 
    <div class="card title-line">
        <div class="card-body"> 
        <ul class="nav nav-tabs border-tab mb-0" id="bottom-tab" role="tablist">
            <li class="nav-item" role="presentation"><a class="nav-link nav-border txt-info tab-info active" id="bottom-inbox-tab" data-bs-toggle="tab" href="#bottom-inbox" role="tab" aria-controls="bottom-inbox" aria-selected="true"><i class="icofont icofont-hat"></i>Matriculas</a></li>
            <li class="nav-item" role="presentation"><a class="nav-link nav-border txt-info tab-info" id="bottom-home-encarregado" data-bs-toggle="tab" href="#bottom-encarregado" role="tab" aria-controls="bottom-encarregado" aria-selected="false" tabindex="-1"><i class="icofont icofont-medical-sign"> </i>Co-Curricular</a></li>
            <li class="nav-item" role="presentation"><a class="nav-link nav-border txt-info tab-info" id="bottom-contact-tab" data-bs-toggle="tab" href="#bottom-contact" role="tab" aria-controls="bottom-contact" aria-selected="false" tabindex="-1"><i class="icofont icofont-man-in-glasses"></i>Pessoas a Contactar</a></li>
            <li class="nav-item" role="presentation"><a class="nav-link nav-border txt-info tab-info" id="bottom-home-tab" data-bs-toggle="tab" href="#bottom-home" role="tab" aria-controls="bottom-home" aria-selected="false" tabindex="-1"><i class="icofont icofont-headphone-alt-2"> </i>Plano Estudo</a></li>
            <li class="nav-item" role="presentation"><a class="nav-link nav-border txt-info tab-info" id="bottom-home-tab-club" data-bs-toggle="tab" href="#bottom-register" role="tab" aria-controls="bottom-home" aria-selected="false" tabindex="-1"><i class="icofont icofont-medical-sign"> </i>Saude</a></li>
            <li class="nav-item" role="presentation"><a class="nav-link nav-border txt-info tab-info" id="bottom-contact-tab1" data-bs-toggle="tab" href="#bottom-contact1" role="tab" aria-controls="bottom-contact1" aria-selected="false" tabindex="-1"><i class="icofont icofont-ui-message"></i>Mensagem</a></li>
            <li class="nav-item" role="presentation"><a class="nav-link nav-border txt-info tab-info" id="bottom-contact-tab2" data-bs-toggle="tab" href="#bottom-contact2" role="tab" aria-controls="bottom-contact2" aria-selected="false" tabindex="-1"><i class="icofont icofont-money"></i>Propinas</a></li>
        </ul>
        <!-- Inscricoes -->
        <div class="tab-content" id="bottom-tabContent">
          <div class="tab-pane fade" id="bottom-register" role="tabpanel" aria-labelledby="bottom-home-tab">
              
              <br>
              <div class="d-flex align-items-center gap-3 pills-blogger" style="border: 5px solid black; box-shadow: 5px 5px 10px gray; padding: 10px" >
                    <div class="col-md-3"> 
                        <br>
                        <b><center>Centro Saúde</center></b>
                        <center>{{$saude->centro_saude}}</center><br>
                    </div>
                    <div class="col-md-4"> 
                      <b>Sofre de alguma doença?</b><span style="padding-left: 3%"> {{$saude->sofre_alguma_doenca}}</span><br>
                      <b>Indicações especiais a ter em conta:</b><span style="padding-left: 3%"> {{$saude->indicacao_especial}}</span><br>
                      <b>Ja teve Varicela?</b><span style="padding-left: 3%"> {{$saude->teve_varicela}}</span><br>
                      <b>Outras?</b><span style="padding-left: 3%"> {{$saude->teve_outras}}</span><br>
                      <b>Sofre de algum tipo de alergias?</b><span style="padding-left: 3%"> {{$saude->tem_alergia}}</span><br>
                      <b>Tem alguma restrições Alimentar?</b><span style="padding-left: 3%"> {{$saude->tem_restricoes_alimentares}}</span><br>
                    </div>

                    <div class="col-md-5"> 
                      <b>Em caso afirmativo, qual?</b><span style="padding-left: 3%"> {{$saude->qual}}</span><br>
                      <b>Já teve Sarampo?</b><span style="padding-left: 3%">{{$saude->teve_sarampo}}</span><br>
                      <b>Ja teve Papeira?</b><span style="padding-left: 3%">{{$saude->teve_papaira}}</span><br>
                      <b>Especifique:</b><span style="padding-left: 3%">{{$saude->especifique_outras}}</span><br>
                      <b>Especifique?</b><span style="padding-left: 3%"> {{$saude->especifique_alergia}}</span><br>
                      <b>Indique qual ou quais?</b><span style="padding-left: 3%"> {{$saude->especifique_restricoes_alimentares}}</span><br>
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
                                  <th scope="col">Ano</th>
                                  <th scope="col">Aulas</th>
                                  <th scope="col">Estado</th>
                                  <th scope="col">Inicio</th>
                                  <th scope="col">Fim</th>
                                  <th scope="col">Data</th>
                                </tr>
                              </thead>
                              <tbody>

                              @foreach($coCurriculares as $coCurricular)
                                <tr>
                                  <td>{{$coCurricular->anoAcademico->ano}}</td>
                                  <td>{{$coCurricular->disciplina->nome}}</td>
                                  <td>
                                      @if($coCurricular->estado==1)
                                          Activo
                                      @else
                                          Cancelado
                                      @endif
                                  </td>
                                  <td>{{$coCurricular->mes->nome}}</td>
                                  <td>{{$coCurricular->mesfim->nome}}</td>
                                  <td>{{$coCurricular->created_at->format('d-M-Y')}}</td>
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
              @if($plano!=null)
              <div class="d-flex align-items-center gap-3 pills-blogger" style="border: 5px solid black; box-shadow: 5px 5px 10px gray; padding: 10px" >
                    <div class="col-md-3"> 
                        
                        <b><center>Plano ed. individualizado</center></b>
                        <center>{{$plano->motivo_plano}}</center><br>
                        <b><center>Indicações especiais em conta</center></b>
                        <center>{{$plano->indicacoes_especial}}</center><br>
                    </div>
                    <div class="col-md-4"> 
                      <b>Tem diagnóstico Autismo?</b><span style="padding-left: 3%"> {{$plano->d_autismo}}</span><br>
                      <b>Tem diagnóstico Dislexia?</b><span style="padding-left: 3%"> {{$plano->d_dislexia}}</span><br>
                      <b>Défice de Atenção e Hiperactividade?</b><span style="padding-left: 3%"> {{$plano->d_hiperatividade}}</span><br>
                      <b>Outras condições?</b><span style="padding-left: 3%"> {{$plano->outras_condicoes}}</span><br>
                      <b>Especifique:</b><span style="padding-left: 3%"> {{$plano->especifica}}</span><br>
                    </div>

                    <div class="col-md-5"> 
                      <b>Comunicação Verbal?</b><span style="padding-left: 3%"> {{$plano->comunicacao_verbal}}</span><br>
                      <b>Jargão?</b><span style="padding-left: 3%">{{$plano->jorgao}}</span><br>
                      <b>Ecolálias?</b><span style="padding-left: 3%">{{$plano->ecolalia}}</span><br>
                      <b>Otorrino:</b><span style="padding-left: 3%">{{$plano->avaliado_otorrino}}</span><br>
                      <b>Notas especiais?</b><span style="padding-left: 3%"> {{$plano->notas_especial}}</span><br>
                   </div>

                   
                   <div class="col-md-5"> 
                      <b>Tolerância ao barrulho?</b><span style="padding-left: 3%"> {{$plano->suporta_barrulho}}</span><br>
                      <b>Hipersencibilidade ao toque?</b><span style="padding-left: 3%">{{$plano->hipersencibilidade_toque}}</span><br>
                      <b>Alguma estereotipia?</b><span style="padding-left: 3%">{{$plano->estoratipia}}</span><br>
                      <b>especifique:</b><span style="padding-left: 3%">{{$plano->estoratipia_especifique}}</span><br>
                   </div>
              </div>
              @else

               <h2><center>Plano de Estudo Normal</center></h2>

              @endif
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
                              <th scope="col">Ano A</th>
                              <th scope="col">Classe</th>
                              <th scope="col">Turma</th>
                              <th scope="col">Referencia</th>
                              <th scope="col">Valor</th>
                              <th scope="col">Data</th>
                              <th scope="col">Acção</th>
                            </tr>
                          </thead>
                          <tbody>

                          @foreach($matriculas as $matricula)
                            <tr>
                              <td>{{$matricula->anoAcademico->ano}}</td>
                              <td>{{$matricula->classe->nome}}</td>
                              <td>
                                
                                @if($matricula->turma_id!=null)
                                   {{$matricula->turma->nome}}
                                @else
                                    ...
                                @endif
                              </td>
                              <td>{{$matricula->referencia}}</td>
                              <td>{{ number_format($matricula->valor_total, 2, ',', '.') }}</td>
                              <td>{{$matricula->created_at->format('d-M-Y')}}</td>
                              <td>

                                    <a class="btn btn-warning btn-xs activate-btn" target="_blank" href="{{route('matricula.facturaPDF',['factura'=>$matricula->pagamento->referencia])}}">
                                            Factura
                                    </a>
                                    @if($matricula->pagou==1)
                                        <a class="btn btn-success btn-xs activate-btn" target="_blank" href="{{route('matricula.reciboPDF',['factura'=>$matricula->pagamento->referencia])}}">
                                            Recibo
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
              </div>
            
            </div>
            <!-- Fim Matriculas -->

            <!-- Recolher Crianca -->
            <div class="tab-pane fade" id="bottom-contact" role="tabpanel" aria-labelledby="bottom-contact-tab">
                 <br>
                 <div class="col-sm-12"> 
                    <div class="card title-line">
                      <div class="card-block row">
                        <div class="col-sm-12 col-lg-12 col-xl-12">
                          <div class="table-responsive custom-scrollbar">
                            <table class="table">
                              <thead class="table-dark">
                                <tr>
                                  <th scope="col">Grau Para.</th>
                                  <th scope="col">Relacao</th>
                                  <th scope="col">Nome</th>
                                  <th scope="col">Telefone</th>
                                  <th scope="col">Email</th>
                                  <th scope="col">Accao</th>
                                </tr>
                              </thead>
                              <tbody>

                              @foreach($pessoasContactar as $pessoaContactar)
                                <tr>
                                  <td>{{$pessoaContactar->grau}}</td>
                                  <td>{{$pessoaContactar->relacao->nome}}</td>
                                  <td>{{$pessoaContactar->pessoa->nome}}</td>
                                  <td>{{$pessoaContactar->pessoa->telefone}}</td>
                                  <td>{{$pessoaContactar->pessoa->email}}</td>
                                  <td>
                                    @if($pessoaContactar->relacao->nome=='Encarregado')
                                      <a class="btn btn-warning btn-xs" href="{{route('mudar.telefone',['codigo'=>$aluno->codigo])}}" >
                                          Mudar Contacto
                                      </a>
                                    @else
                                      ...
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
            <!-- Fim Recolher Crianca -->

            <!-- Recolher Crianca -->
            <div class="tab-pane fade" id="bottom-contact1" role="tabpanel" aria-labelledby="bottom-contact-tab">
                 <br>
                 <div class="col-sm-12"> 
                    <div class="card title-line">
                      <div class="card-block row">
                        <div class="col-sm-12 col-lg-12 col-xl-12">
                          <div class="table-responsive custom-scrollbar">
                            <table class="table">
                              <thead class="table-dark">
                                <tr>
                                  <th scope="col">Contacto</th>
                                  <th scope="col">Canal</th>
                                  <th scope="col">QTD</th>
                                  <th scope="col">Credito</th>
                                  <th scope="col">Descricao</th>
                                  <th scope="col">Estado</th>
                                  <th scope="col">Data</th>
                                  <th scope="col">Hora</th>
                                </tr>
                              </thead>
                              <tbody>

                                @foreach($mensagens as $mensagem)
                                  <tr>
                                    <td>{{$mensagem->telefone}}</td>
                                    <td>{{$mensagem->canal}}</td>
                                    <td>{{$mensagem->qtd}}</td>
                                    <td>{{$mensagem->credito }}</td>
                                    <td >
                                      <i data-container="body" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="{{$mensagem->descricao}}">{{ Str::limit($mensagem->descricao, 40) }}</i>
                                    </td>
                                    <td>{{$mensagem->tipo}}</td>
                                    <td>{{$mensagem->updated_at->format('d-M-Y')}}</td>
                                    <td>{{$mensagem->updated_at->format('H:s')}}</td>
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
            <!-- Fim Recolher Crianca -->

            <!-- Recolher Propina -->
            <div class="tab-pane fade" id="bottom-contact2" role="tabpanel" aria-labelledby="bottom-contact-tab">
                 <br>
                 <div class="col-sm-12"> 
                    <div class="card title-line">
                      <div class="card-block row">
                        <div class="col-sm-12 col-lg-12 col-xl-12">
                          <div class="table-responsive custom-scrollbar">
                            <table class="table">
                              <thead class="table-dark">
                                <tr>
                                    <th></th>
                                    <th>Propina Pendente</th>
                                    <th>Prazo</th>
                                    <th>Curricular</th>
                                    <th>Co-Curr</th>
                                    <th>Multa</th>
                                    <th>Total</th>
                                    <th>Referência</th>
                                    @role('Secretaria') 
                                     <th>Acção</th>
                                    @endrole
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
                                        @role('Secretaria')   
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
                                        @endrole
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
            <!-- Fim Recolher Propinas -->

            <div class="modal fade" id="modalTransferencia" tabindex="-1">

       @role('Secretaria')

          <div class="modal-dialog modal-lg">

              <div class="modal-content">

                  <form method="POST" id="form" enctype="multipart/form-data">

                      @csrf

                      <div class="modal-header">

                          <h5 class="modal-title">
                              Transferência de Aluno
                          </h5>

                          <button class="btn-close"
                                  data-bs-dismiss="modal"></button>

                      </div>

                      <div class="modal-body">

                          <input type="hidden"
                                name="aluno_id"
                                id="aluno_id">

                          <div class="row">

                              <div class="col-md-12 mb-3">

                                  <input type="hidden" name="codigo" value="{{$aluno->codigo}}">

                                  <label>Aluno</label>

                                  <input
                                      type="text"
                                      id="nomeAluno"
                                      class="form-control"
                                      value="{{$aluno->pessoa->nome}}"
                                      readonly>

                              </div>

                              <div class="col-md-6">

                                  <label>Escola Destino</label>

                                  <select
                                      name="escolaDestino"
                                      id="escolaDestino"
                                      class="form-select"
                                      required>

                                      @foreach($escolas as $escola)

                                          <option value="{{$escola->id}}">
                                              {{$escola->nome}}
                                          </option>

                                      @endforeach

                                  </select>

                              </div>

                              <div class="col-md-6">

                                  <label>Classe</label>

                                  <select
                                      name="classe"
                                      id="classeDestino"
                                      class="form-select"
                                      required>

                                      @foreach($classes as $classe)

                                        @if($matriculaActual->classe_id == $classe->id)
                                          <option value="{{$classe->id}}">
                                              {{$classe->nome}} 
                                          </option>
                                        @endif  

                                      @endforeach

                                  </select>

                              </div>

                              <div class="col-md-6 mt-3">

                                  <label>Turma</label>

                                  <select
                                      name="turma"
                                      id="turmaDestino"
                                      class="form-select"
                                      required>

                                     @foreach($turmas as $turma)

                                          <option value="{{$turma->id}}">
                                              {{$turma->nome}} 
                                          </option>

                                      @endforeach

                                  </select>

                              </div>

                              <div class="col-md-6 mt-3">

                                  <label>Data da Transferência</label>

                                  <input
                                      type="date"
                                      name="data"
                                      class="form-control"
                                      value="{{date('Y-m-d')}}">

                              </div>

                              <div class="col-md-12 mt-3">

                                  <label>Motivo</label>

                                  <textarea
                                      class="form-control"
                                      rows="3"
                                      name="motivo"></textarea>

                              </div>

                          </div>

                      </div>

                      <div class="modal-footer">

                          <button
                              class="btn btn-secondary"
                              data-bs-dismiss="modal">

                              Cancelar

                          </button>

                          <button id="botao_salvar" type="submit" class="btn btn-primary">
                              <span id="botao_texto">{{__('Confirmar Transferência')}}</span>
                              <i id="icon_enviar" class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
                          </button>


                </div>

            </form>

          </div>

       @endrole

    </div>

</div>
            
        </div>
        </div>
    </div>
</div>

@endsection

@push('js')


<script>
document.querySelectorAll('.btn-referencia').forEach(btn => {
    btn.addEventListener('click', function () {

        let codigo = this.dataset.codigo;

        Swal.fire({
            title: 'Enviar referência?',
            text: 'Esta ação vai enviar a referência de pagamento da matrícula ao encarregado via SMS.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim, enviar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {

            if (result.isConfirmed) {

                Swal.fire({
                    title: 'Aguarde...',
                    text: 'A enviar referência...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                fetch(`/matricula/aluno/${codigo}`, {
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

<script>
document.querySelectorAll('.btn-gerar-referencia').forEach(btn => {

    btn.addEventListener('click', function () {

        const codigoAluno = this.dataset.codigo;

        Swal.fire({
            title: 'Gerar referência?',
            html: `
                Esta ação irá gerar a <strong>referência de pagamento</strong>
                para a <strong>classe atual do aluno</strong>.<br><br>
                Deseja continuar?
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim, gerar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {

            if (result.isConfirmed) {
                // 👉 redireciona para a rota Laravel
                window.location.href = `/referencia/propina/${codigoAluno}`;
            }

        });
    });

});
</script>

<script>
document.querySelectorAll('.btn-gerar-referencia').forEach(btn => {

    btn.addEventListener('click', function () {

        const codigoAluno = this.dataset.codigo;
        const aluno = this.dataset.aluno;
        const classe = this.dataset.classe;

        // 1️⃣ CONFIRMAÇÃO (IGUAL AO OUTRO SCRIPT)
        Swal.fire({
            title: 'Gerar referência?',
            html: `
                <strong>Classe:</strong> ${classe}<br><br>
                Esta ação irá gerar <strong>referência de pagamento</strong>
                da mensalidade de aluno <strong>${aluno}.</strong><br><br>
                Deseja continuar?
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim, gerar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {

            if (!result.isConfirmed) return;

            // 2️⃣ EXECUTA A AÇÃO (BACKEND)
            fetch(`/referencia/propina/${codigoAluno}`, {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(response => {

                // 3️⃣ MOSTRA RESULTADO
                Swal.fire({
                    icon: response.status ? '1' : '0',
                    title: response.status ? 'Sucesso' : 'Erro',
                    text: response.message,
                    confirmButtonText: 'OK'
                }).then(() => {

                    // 4️⃣ SÓ DEPOIS DO OK
                    if (response.status) {
                        window.location.reload();
                    }

                });

            })
            .catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Erro ao comunicar com o servidor.',
                    confirmButtonText: 'OK'
                });
            });

        });
    });

});
</script>

<script>
document.querySelectorAll('.mudar-classe').forEach(btn => {
    btn.addEventListener('click', function (e) {
        e.preventDefault();

        let alunoId = this.getAttribute('data-id');

        Swal.fire({
            title: 'Mudar de Classe',
            html: `
                <select id="classe" class="swal2-input">
                    <option value="">Selecione a classe</option>
                    @foreach($classes as $classe)
                        <option value="{{ $classe->id }}">{{ $classe->nome }}</option>
                    @endforeach
                </select>
            `,
            confirmButtonText: 'Salvar',
            showCancelButton: true,
            preConfirm: () => {
                const classe = document.getElementById('classe').value;

                if (!classe) {
                    Swal.showValidationMessage('Selecione uma classe');
                }

                return { classe: classe };
            }
        }).then((result) => {
            if (result.isConfirmed) {

                fetch('/aluno/mudar-classe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        aluno_id: alunoId,
                        classe_id: result.value.classe
                    })
                })
                .then(res => res.json())
                .then(data => {
                    Swal.fire('Sucesso!', 'Classe atualizada', 'success')
                        .then(() => location.reload());
                });
            }
        });

    });
});
</script>


<script>
document.querySelectorAll('.mudar-foto').forEach(btn => {

    btn.addEventListener('click', function(e) {

        e.preventDefault();

        let alunoId = this.getAttribute('data-id');
        let fotoAtual = this.getAttribute('data-foto');

        let fotoUrl = '/avatar/' + fotoAtual;

        Swal.fire({
            title: 'Mudar Foto do Aluno',
            html: `

                <div style="text-align:center;margin-bottom:15px;">
                    <img id="preview"
                         src="${fotoUrl}"
                         class="img-80 b-r-30"
                         style="
                            width:80px;
                            height:80px;
                            object-fit:cover;
                            border-radius:30px;
                            border:1px solid #ddd;
                         ">
                </div>

                <input type="file"
                       id="foto"
                       class="swal2-file"
                       accept="image/*">

            `,
            showCancelButton: true,
            confirmButtonText: 'Salvar',

            didOpen: () => {

                const fotoInput = document.getElementById('foto');

                fotoInput.addEventListener('change', function() {

                    const file = this.files[0];

                    if(file){

                        const reader = new FileReader();

                        reader.onload = function(e){

                            document.getElementById('preview').src = e.target.result;

                        };

                        reader.readAsDataURL(file);

                    }

                });

            },

            preConfirm: () => {

                const foto = document.getElementById('foto').files[0];

                if (!foto) {
                    Swal.showValidationMessage('Selecione uma foto');
                    return false;
                }

                return foto;
            }

        }).then((result) => {

            if(result.isConfirmed){

                let formData = new FormData();

                formData.append('aluno_id', alunoId);
                formData.append('foto', result.value);

                fetch('/aluno/mudar-foto', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {

                    Swal.fire(
                        'Sucesso!',
                        'Foto atualizada com sucesso.',
                        'success'
                    ).then(() => {
                        location.reload();
                    });

                })
                .catch(error => {

                    Swal.fire(
                        'Erro',
                        'Não foi possível atualizar a foto.',
                        'error'
                    );

                });

            }

        });

    });

});
</script>

<script>

$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("#form").validate({
        rules: {
            motivo: {
                required: true,
            }
        },
        submitHandler: function(form) {
            // Use FormData para suportar envio de arquivos
            var formData = new FormData(form);

            $.ajax({
                type: "POST",
                url: "{{ route('transferencia.aluno') }}",
                data: formData,
                processData: false, // Não processa os dados, necessário para FormData
                contentType: false, // Define o content type como multipart/form-data
                beforeSend: function() {
                    $('#botao_salvar').attr('disabled', true);
                    $('#icon_enviar').removeClass('ri-arrow-right-line').addClass('spinner-border ri-loader-2-line');
                    $('#botao_texto').text('Registando Funcionario...');
                },
                success: function(response) {
                    $('#botao_salvar').attr('disabled', false);
                    $('#icon_enviar').removeClass('spinner-border ri-loader-2-line').addClass('ri-arrow-right-line');
                    $('#botao_texto').text('Entrar');

                    if (response.status == 1) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: response.message,
                        });
                        window.location.reload();
                    } else if (response.status == 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro!',
                            text: response.message,
                        });
                    }
                },
                error: function(errors) {
                    $('#botao_salvar').attr('disabled', false);
                    $('#icon_enviar').removeClass('spinner-border ri-loader-2-line').addClass('ri-arrow-right-line');
                    $('#botao_texto').text('Entrar');
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



@endpush
