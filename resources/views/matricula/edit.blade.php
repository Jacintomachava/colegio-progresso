@extends('layouts.app')

@push('css')

<style type="text/css">

.area{
    margin: 10px auto;
    box-shadow: 0 10px 100px #ccc;
    padding: 20px;
    box-sizing: border-box;
    max-width: 500px;
}

.area video{
    width: 100%;
    height: auto;
    background-color: whitesmoke;
}

.area textarea{
    width: 100%;
    margin-top: 10px;
    height: 80px;
    box-sizing: border-box;
}

.area button{
    -webkit-appearance: none;
    width: 100%;
    box-sizing: border-box;
    padding: 10px;
    text-align: center;
    background-color: #068c84;
    color: white;
    text-transform: uppercase;
    border: 1px solid white;
    box-shadow: 0 1px 5px #666;
}

.area button:focus{
    outline: none;
    background-color: #0989b0;
}

.area img{
    max-width: 100%;
    height: 400px;
}

.area .caminho-imagem{
    padding: 5px 10px;
    border-radius: 3px;
    background-color: #068c84;
    text-align: center;
}

.area .caminho-imagem a{
    color: white;
    text-decoration: none;
}

.area .caminho-imagem a:hover{
    color: yellow;
}
</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

@endpush

@section('conteudo')


    <h2>
        <div class="col-12">
          <div class="error" id="error" style="color: red; text-align: center">
          </div>
        </div>
    </h2>

    <div class="container-fluid">
        <div class="row">
          <div class="col-xl-12">
            <div class="card height-equal title-line">
              <div class="card-header">
                <h2>Matricular Aluno</h2>
              </div>
              <div class="card-body basic-wizard important-validation">
                <div class="stepper-horizontal custom-scrollbar" id="stepper1">
                  <div class="stepper-one stepper step editing active">
                    <div class="step-circle"><span>1</span></div>
                    <div class="step-title">Dados do Aluno</div>
                    <div class="step-bar-left"></div>
                    <div class="step-bar-right"></div>
                  </div>
                  <div class="stepper-two step">
                    <div class="step-circle"><span>2</span></div>
                    <div class="step-title">Plano Educativo Individualizado</div>
                    <div class="step-bar-left"></div>
                    <div class="step-bar-right"></div>
                  </div>
                  <div class="stepper-three step">
                    <div class="step-circle"><span>3</span></div>
                    <div class="step-title">Saúde</div>
                    <div class="step-bar-left"></div>
                    <div class="step-bar-right"></div>
                  </div>
                </div>
                <div id="msform1">
                  <form class="row g-3 needs-validation custom-input" id="msform" enctype="multipart/form-data">

                    @csrf

                    <input class="form-control"  type="hidden" name="aluno" value="{{$matricula->aluno->id}}" >
                    <input class="form-control"  type="hidden" name="matricula" value="{{$matricula->id}}" >
                    
                    <form1 class="stepper-one row g-3 needs-validation custom-input" >

                      <div class="col-4">
                        <br>
                        <label class="form-label" for="email-basic-wizard">Nome de Aluno<span class="txt-danger">*</span></label>
                        <input class="form-control"  type="text" name="nomealuno" value="{{$matricula->aluno->pessoa->nome}}" placeholder="Nome de Aluno">
                        <label class="form-label" for="email-basic-wizard">Nome de Casa</label>
                        <input class="form-control"  type="text" name="nome_casa" value="{{$matricula->aluno->nome_casa}}" placeholder="Nome de Casa do Aluno">
                        <label class="form-label" for="passwordwizard">Genero<span class="txt-danger">*</span></label>
                          <select class="form-select"  name="genero">
                                <option value="">Seleciona o Genero</option>
                                <option value="Masculino" @if($matricula->aluno->pessoa->genero=='Masculino') selected @endif>Masculino</option>
                                <option value="Femenino" @if($matricula->aluno->pessoa->genero=='Femenino') selected @endif>Femenino</option>
                          </select>
                        <label class="form-label" for="firstnamewizard">Data de Nascimento<span class="txt-danger">*</span></label>
                        <input class="form-control"  type="date" name="data_nascimento" value="{{$matricula->aluno->pessoa->data_nascimento}}" placeholder="Data de Nascimento">

                      </div>

                      <div class="col-4">
                          <div class="area">
                              <button type="button" id="capture">Tirar foto</button>
                              <center>
                                  <video id="video" style="width: 100%; height: 40%;"></video>
                              </center>  
                                      
                          </div> 
                      </div>
                      <div class="col-4">
                            <div class="area" >
                                <button type="button" id="capture2">Pre-Visualizar</button>
                                <center>
                                <canvas id="canvas" style="width: 100%; height: 40%;" >Pré-visualização</canvas> 
                                </center>        
                            </div>
                      </div>


                      <div class="col-4">
                         <label class="form-label" for="passwordwizard">Provincia<span class="txt-danger">*</span></label>
                           <select class="form-select"  name="provincia">
                              <option value="">Escolhe a Provincia...</option>
                              @foreach($provincias as $provincia)
                                <option value="{{$provincia->id}}" @if($matricula->aluno->provincia_id == $provincia->id) selected @endif>
                                    {{$provincia->nome}}
                                </option>
                              @endforeach
                           </select>
                      </div>


                      <div class="col-4">
                        <label class="form-label" for="passwordwizard">Tipo Documento<span class="txt-danger">*</span></label>
                          <select class="form-select"  name="tipo_documento">
                                <option value="">Seleciona o tipo de Documento</option>
                                <option value="B.I" @if($matricula->aluno->pessoa->tipo_documento=='B.I') selected @endif>B.I</option>
                                <option value="Cédula" @if($matricula->aluno->pessoa->tipo_documento=='Cédula') selected @endif>Cédula</option>
                                <option value="Boletim de Nascimento" @if($matricula->aluno->pessoa->tipo_documento=='Boletim de Nascimento') selected @endif>Boletim de Nascimento</option>
                                <option value="Boletim de Nascimento" @if($matricula->aluno->pessoa->tipo_documento=='Passaporte') selected @endif>Passaporte</option>
                          </select>
                      </div>

                      <div class="col-4">
                        <label class="form-label" for="confirmpasswordwizard">Número Documento</label>
                        <input class="form-control"  type="text" name="numero_documento" value="{{$matricula->aluno->pessoa->numero_documento}}" placeholder="Número Documento" >
                      </div>

                      <div class="col-4">
                        <label class="form-label" for="confirmpasswordwizard">Endereço/Bairro<span class="txt-danger">*</span></label>
                        <input class="form-control"  type="text" name="endereco" value="{{$matricula->aluno->pessoa->endereco}}" placeholder="Endereço/Bairro" >
                      </div>

                      <div class="col-4">
                        <label class="form-label" for="passwordwizard">Classe<span class="txt-danger">*</span></label>
                          <select class="form-select"  name="classe" id="classe" onchange="getTurmas()">
                              <option value="">Escolhe a Classe...</option>
                              @foreach($classes as $classe)
                                <option value="{{$classe->id}}" @if($matricula->classe_id == $classe->id) selected @endif>{{$classe->nome}}</option>
                              @endforeach

                          </select>
                      </div>

                      <div class="col-4">
                        <label class="form-label" for="passwordwizard">Regime<span class="txt-danger">*</span></label>
                          <select class="form-select"  name="turma" id="turma">
                              <option value="">Escolhe o Regime...</option>
                              @foreach($turmas as $turma)
                                <option value="{{$turma->id}}" @if($matricula->turma_id == $turma->id) selected @endif>{{$turma->nome}} - {{$turma->periodo->detalhe}}</option>
                              @endforeach
                          </select>
                      </div>

                      <div class="col-4">
                        <label class="form-label" for="passwordwizard">P. E. Individualizado<span class="txt-danger">*</span></label>
                          <select class="form-select"  name="plano" id="plano" onchange="mostrarPlano()">
                              <option value="">Escolha o plano de estudo</option>
                              <option value="1" @if($matricula->plano=='1') selected @endif>Plano Normal</option>
                              <option value="2" @if($matricula->plano=='2') selected @endif>Plano Gold</option>
                          </select>
                      </div>
                      @php
                          $irmaosIds = $irmaos->pluck('aluno_id')->toArray();
                      @endphp

                      <div class="form-group col-4">
                          <label for="contactos" class="form-label">{{ __('Alunos Irmãos') }} </label>
                          <select class="form-select contactos" name="alunos[]"  multiple="multiple" >
                              @foreach ($alunos as $aluno)
                                  <option value="{{ $aluno->id }}" 
                                      {{ in_array($aluno->id, $irmaosIds) ? 'selected' : '' }}>
                                      {{ $aluno->pessoa->nome }} ({{ $aluno->codigo }})
                                  </option>
                              @endforeach
                          </select>
                      </div>

                      @php
                          $diciplinasIds = $coCurriculares->pluck('disciplina_id')->toArray();
                      @endphp

                      <div class="form-group col-4">
                          <label for="contactos" class="form-label">{{ __('Disciplina Co-Curriculares') }} </label>
                          <select class="form-select contactos1" name="cocurriculares[]"  multiple="multiple" >
                              @foreach ($disciplinas as $disciplina)
                                  <option value="{{ $disciplina->id }}"
                                      {{ in_array($disciplina->id,$diciplinasIds) ? 'selected' : '' }}>
                                      {{ $disciplina->nome }} 
                                  </option>
                              @endforeach
                          </select>
                      </div>

                    </form1>

                    <form1 class="stepper-two row g-3 needs-validation custom-input">
                        <!-- Divs específicas -->
                        <div id="planoNormal" @if($matricula->plano=='1') @else style="display: none; margin-top: 10px;"  @endif >
                          <div class="d-grid gap-2 buttons-box">
                            <button class="btn btn-light-primary" type="button">Foi Selecionado O Plano de Estudo Normal</button>
                          </div>
                        </div>
                        <div id="planoGold"  @if($matricula->plano=='2') @else  style="display: none; margin-top: 10px;"  @endif>
                         <div class="row">

                         <div class="row border rounded-3" style="padding: 8pt; margin-left: 8pt; background-color: #f2eeed ">   
                          <div class="col-sm-6">
                            <label class="form-label" for="firstnamewizard">Motivo de Plano educativo individualizado</label>
                            <input class="form-control"  type="text" @if($planoEducativoIndividualizada!=null) value="{{$planoEducativoIndividualizada->motivo_plano}}" @endif  name="motivo_plano_individualizado" placeholder="Motivo de Plano educativo individualizado">
                          </div>

                          <div class="col-sm-6">
                            <label class="form-label" for="firstnamewizard">Indicações especiais a ter em conta</label>
                            <input class="form-control"  type="text" name="indicacoes_especiais" @if($planoEducativoIndividualizada!=null) value="{{$planoEducativoIndividualizada->indicacoes_especial}}" @endif placeholder="Indicações especiais a ter em conta">
                          </div>

                          <div class="col-3">
                            <label class="form-label" for="passwordwizard">diagnóstico Autismo?</label>
                            <select class="form-select"  name="autismo">
                                <option value="Não" @if($planoEducativoIndividualizada!=null) @if($planoEducativoIndividualizada->d_autismo=='Não') selected @endif @endif>Não</option>
                                <option value="Sim" @if($planoEducativoIndividualizada!=null) @if($planoEducativoIndividualizada->d_autismo=='Sim') selected @endif @endif>Sim</option>
                            </select>
                          </div>

                          <div class="col-3">
                            <label class="form-label" for="passwordwizard">diagnóstico Dislexia?</label>
                            <select class="form-select"  name="dislexia">
                                <option value="Não" @if($planoEducativoIndividualizada!=null) @if($planoEducativoIndividualizada->d_dislexia=='Não') selected @endif @endif>Não</option>
                                <option value="Sim" @if($planoEducativoIndividualizada!=null) @if($planoEducativoIndividualizada->d_dislexia=='Sim') selected @endif @endif>Sim</option>
                            </select>
                          </div>

                          <div class="col-3">
                            <label class="form-label" for="passwordwizard">diagnóstico Hiperactividade?</label>
                            <select class="form-select"  name="hiperactividade">
                                <option value="Não" @if($planoEducativoIndividualizada!=null) @if($planoEducativoIndividualizada->d_hiperatividade=='Não') selected @endif @endif>Não</option>
                                <option value="Sim" @if($planoEducativoIndividualizada!=null) @if($planoEducativoIndividualizada->d_hiperatividade=='Sim') selected @endif @endif>Sim</option>
                            </select>
                          </div>

                          <div class="col-3">
                            <label class="form-label" for="passwordwizard">Outras condições?</label>
                            <select class="form-select"  name="outras_condicoes">
                                <option value="Não" @if($planoEducativoIndividualizada!=null) @if($planoEducativoIndividualizada->outras_condicoes=='Não') selected @endif @endif>Não</option>
                                <option value="Sim" @if($planoEducativoIndividualizada!=null) @if($planoEducativoIndividualizada->outras_condicoes=='Sim') selected @endif @endif>Sim</option>
                            </select>
                          </div>

                          <div class="col-sm-12">
                            <label class="form-label" for="firstnamewizard">Especifique (Outras condições)</label>
                            <input class="form-control"  type="text" name="especifica_outras_condicoes" @if($planoEducativoIndividualizada!=null) value="{{$planoEducativoIndividualizada->especifica}}" @endif placeholder="Especifique (Outras condições)">
                          </div>
                         </div>

                         <div class="row border rounded-3" style="padding: 8pt; margin-left: 8pt;margin-top: 14pt; background-color: #a3d2f7 "> 
                          <div class="col-3">
                            <label class="form-label" for="passwordwizard">Comunicação Verbal</label>
                            <select class="form-select"  name="comunicacao_verbal">
                                <option value="Não" @if($planoEducativoIndividualizada!=null) @if($planoEducativoIndividualizada->comunicacao_verbal=='Não') selected @endif @endif>Não</option>
                                <option value="Sim" @if($planoEducativoIndividualizada!=null) @if($planoEducativoIndividualizada->comunicacao_verbal=='Sim') selected @endif @endif>Sim</option>
                            </select>
                          </div>

                          <div class="col-3">
                            <label class="form-label" for="passwordwizard">Jargão</label>
                            <select class="form-select"  name="jargao">
                                <option value="Não"  @if($planoEducativoIndividualizada!=null) @if($planoEducativoIndividualizada->jorgao=='Não') selected @endif @endif>Não</option>
                                <option value="Sim"  @if($planoEducativoIndividualizada!=null) @if($planoEducativoIndividualizada->jorgao=='Sim') selected @endif @endif>Sim</option>
                            </select>
                          </div>

                          <div class="col-3">
                            <label class="form-label" for="passwordwizard">Ecolálias</label>
                            <select class="form-select"  name="ecolalia">
                                <option value="Não" @if($planoEducativoIndividualizada!=null) @if($planoEducativoIndividualizada->ecolalia=='Não') selected @endif @endif>Não</option>
                                <option value="Sim" @if($planoEducativoIndividualizada!=null) @if($planoEducativoIndividualizada->ecolalia=='Sim') selected @endif @endif>Sim</option>
                            </select>
                          </div>

                          <div class="col-3">
                            <label class="form-label" for="passwordwizard">Otorrino</label>
                            <select class="form-select"  name="otorrino">
                                <option value="Não" @if($planoEducativoIndividualizada!=null) @if($planoEducativoIndividualizada->avaliado_otorrino=='Não') selected @endif @endif>Não</option>
                                <option value="Sim" @if($planoEducativoIndividualizada!=null) @if($planoEducativoIndividualizada->avaliado_otorrino=='Sim') selected @endif @endif>Sim</option>
                            </select>
                          </div>

                          <div class="col-sm-12">
                            <label class="form-label" for="firstnamewizard">Notas especiais</label>
                            <input class="form-control"  type="text" name="notas_especiais" @if($planoEducativoIndividualizada!=null) value="{{$planoEducativoIndividualizada->notas_especial}}" @endif placeholder="Notas especiais">
                          </div>
                        </div>

                        <div class="row border rounded-3" style="padding: 8pt; margin-left: 8pt;margin-top: 14pt; background-color: #f1affa "> 
                          <div class="col-3">
                            <label class="form-label" for="passwordwizard">tolerância ao barrulho</label>
                            <select class="form-select"  name="barrulho">
                                <option value="Não" @if($planoEducativoIndividualizada!=null) @if($planoEducativoIndividualizada->suporta_barrulho=='Não') selected @endif @endif>Não</option>
                                <option value="Sim" @if($planoEducativoIndividualizada!=null) @if($planoEducativoIndividualizada->suporta_barrulho=='Sim') selected @endif @endif>Sim</option>
                            </select>
                          </div>

                          <div class="col-3">
                            <label class="form-label" for="passwordwizard">hipersencibilidade</label>
                            <select class="form-select"  name="hipersencibilidade">
                                <option value="Não" @if($planoEducativoIndividualizada!=null) @if($planoEducativoIndividualizada->hipersencibilidade_toque=='Não') selected @endif @endif>Não</option>
                                <option value="Sim" @if($planoEducativoIndividualizada!=null) @if($planoEducativoIndividualizada->hipersencibilidade_toque=='Sim') selected @endif @endif>Sim</option>
                            </select>
                          </div>

                          <div class="col-3">
                            <label class="form-label" for="passwordwizard">alguma estereotipia</label>
                            <select class="form-select"  name="estereotipia">
                                <option value="Não"  @if($planoEducativoIndividualizada!=null) @if($planoEducativoIndividualizada->estoratipia=='Não') selected @endif @endif>Não</option>
                                <option value="Sim"  @if($planoEducativoIndividualizada!=null) @if($planoEducativoIndividualizada->estoratipia=='Sim') selected @endif @endif>Sim</option>
                            </select>
                          </div>

                          <div class="col-sm-3">
                            <label class="form-label" for="firstnamewizard">Se sim, especifique</label>
                            <input class="form-control"  type="text" @if($planoEducativoIndividualizada!=null) value="{{$planoEducativoIndividualizada->estoratipia_especifique}}" @endif name="estereotpia_especifique" placeholder="Motivo de Plano educativo individualizado">
                          </div>

                        </div>

                        </div>
                      </div>
                  </form1>

                    <form1 class="stepper-three row g-3 needs-validation custom-input" >
                      
                      <div class="col-sm-3">
                        <label class="form-label" for="firstnamewizard">Nome do Centro de Saúde</label>
                        <input class="form-control"  type="text" name="nomecentrosaude" value="{{$saude->centro_saude}}" placeholder="Nome do Centro de Saúde">
                      </div>

                      <div class="col-3">
                         <label class="form-label" for="passwordwizard">Sofre de Alguma Doenca?</label>
                         <select class="form-select"  name="algumadoenca">
                            <option value="Não" @if($saude->sofre_alguma_doenca=='Não') selected @endif>Não</option>
                            <option value="Sim" @if($saude->sofre_alguma_doenca=='Sim') selected @endif>Sim</option>
                         </select>
                      </div>

                      <div class="col-sm-3">
                        <label class="form-label" for="firstnamewizard">Qual</label>
                        <input class="form-control"  type="text" name="qual" value="{{$saude->qual}}" placeholder="Qual">
                      </div>

                      <div class="col-sm-3">
                        <label class="form-label" for="firstnamewizard">Indicações especiais</label>
                        <input class="form-control"  type="text" name="indicacaoespecial" value="{{$saude->indicacao_especial}}" placeholder="Indicações especiais a ter em conta">
                      </div>

                      <div class="col-3">
                         <label class="form-label" for="passwordwizard">Já teve Sarampo</label>
                         <select class="form-select"  name="sarampo">
                            <option value="Não" @if($saude->teve_sarampo=='Não') selected @endif>Não</option>
                            <option value="Sim" @if($saude->teve_sarampo=='Sim') selected @endif>Sim</option>
                         </select>
                      </div>

                      <div class="col-3">
                         <label class="form-label" for="passwordwizard">Já teve Varicela?</label>
                         <select class="form-select"  name="varicela">
                            <option value="Não" @if($saude->teve_varicela=='Não') selected @endif>Não</option>
                            <option value="Sim" @if($saude->teve_varicela=='Sim') selected @endif>Sim</option>
                         </select>
                      </div>

                      <div class="col-3">
                         <label class="form-label" for="passwordwizard">Já teve Papeira?</label>
                         <select class="form-select"  name="papeira">
                            <option value="Não"  @if($saude->teve_papaira=='Não') selected @endif>Não</option>
                            <option value="Sim"  @if($saude->teve_papaira=='Sim') selected @endif>Sim</option>
                         </select>
                      </div>

                      <div class="col-3">
                         <label class="form-label" for="passwordwizard">Outras</label>
                         <select class="form-select"  name="outrasdoencas">
                            <option value="Não" @if($saude->teve_outras=='Não') selected @endif>Não</option>
                            <option value="Sim" @if($saude->teve_outras=='Sim') selected @endif>Sim</option>
                         </select>
                      </div>

                      <div class="col-sm-3">
                        <label class="form-label" for="firstnamewizard">Especifique</label>
                        <input class="form-control"  type="text" name="especificaoutras" value="{{$saude->especifique_outras}}" placeholder="Especifique">
                      </div>

                      <div class="col-3">
                         <label class="form-label" for="passwordwizard">Algum tipo de alergias?</label>
                         <select class="form-select"  name="alergia">
                            <option value="Não" @if($saude->tem_alergia=='Não') selected @endif>Não</option>
                            <option value="Sim" @if($saude->tem_alergia=='Sim') selected @endif>Sim</option>
                         </select>
                      </div>

                      <div class="col-sm-3">
                        <label class="form-label" for="firstnamewizard">Especifique</label>
                        <input class="form-control"  type="text" name="especificaalergia" value="{{$saude->especifique_alergia}}" placeholder="Especifique">
                      </div>

                      <div class="col-3">
                         <label class="form-label" for="passwordwizard">Alguma restrições alimentar?</label>
                         <select class="form-select"  name="restricaoalimentar">
                            <option value="Não" @if($saude->tem_restricoes_alimentares=='Não') selected @endif>Não</option>
                            <option value="Sim" @if($saude->tem_restricoes_alimentares=='Sim') selected @endif>Sim</option>
                         </select>
                      </div>

                      <div class="col-sm-6">
                        <label class="form-label" for="firstnamewizard">Especifique Restrições alimentar</label>
                        <input class="form-control"  type="text" name="especifiquerestricaoalimentar" value="{{$saude->especifique_restricoes_alimentares}}" placeholder="Especifique">
                      </div>


                      <button id="botao_salvar" type="submit" class="btn btn-success w-100">
                          <span id="botao_texto">{{__('Actualizar Matricula do Aluno')}}</span>
                          <i id="icon_enviar" class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
                      </button>
                    </form1>
                    
                   </form>  
                  </div>
                  <br>
                  <div class="wizard-footer d-flex gap-2 justify-content-end">
                    <button class="btn btn-light-primary" id="backbtn" onclick="backStep()"> Voltar</button>
                    <button class="btn btn-primary" id="nextbtn" onclick="nextStep()">Próximo</button>
                  </div>
              </div>
            </div>
          </div>

          <!--  Colocar escondindo -->
          <div class="col-xl-6" hidden>
            <div class="card height-equal title-line">
              <div class="card-header">
                <h2>Student Validation Form</h2>
                <p class="f-m-light mt-1">
                    Please make sure fill all the filed before click on next button.</p>
              </div>
              <div class="card-body custom-input">
                <form class="form-wizard" >
                  <div class="tab">
                    <div class="row g-3">
                      <div class="col-sm-6">
                        <label for="name">Name</label>
                        <input class="form-control" name="name" type="text" placeholder="Enter your name" >
                      </div>
                      <div class="col-sm-6">
                        <label class="form-label" for="student-email-wizard">Email<span class="txt-danger">*</span></label>
                        <input class="form-control" name="email" type="email"  placeholder="johan@gmail.com">
                      </div>
                      <div class="col-12">
                        <label class="form-label" for="password-wizard">Password<span class="txt-danger">*</span></label>
                        <input class="form-control" id="password-wizard" type="password" placeholder="Enter password" required="">
                      </div>
                      <div class="col-12">
                        <label class="form-label" for="confirmpassowrd">Confirm Password<span class="txt-danger">*</span></label>
                        <input class="form-control" id="confirmpassowrd" type="password" placeholder="Enter confirm password" required="">
                      </div>
                    </div>
                  </div>
                  <div class="tab">
                    <div class="row g-3 avatar-upload">
                      <div class="col-12">
                        <div>
                          <div class="avatar-edit">
                            <input id="imageUpload" type="file" accept=".png, .jpg, .jpeg">
                            <label for="imageUpload"></label>
                          </div>
                          <div class="avatar-preview">
                            <div id="image"></div>
                          </div>
                        </div>
                        <h6>Add Profile</h6>
                      </div>
                      <div class="col-12">
                        <label class="form-label" for="exampleFormControlInput1">Portfolio URL</label>
                        <input class="form-control" id="exampleFormControlInput1" type="url" placeholder="https://yuri">
                      </div>
                      <div class="col-12"> 
                        <label class="form-label" for="projectDescription">Project Description</label>
                        <textarea class="form-control" id="projectDescription" rows="2"></textarea>
                      </div>
                    </div>
                  </div>
                  <div class="tab">
                    <h5 class="mb-2">Social Links </h5>
                    <div class="row g-3">
                      <div class="col-sm-6">
                        <label class="form-label" for="twitterControlInput">Twitter</label>
                        <input class="form-control" id="twitterControlInput" type="url" placeholder="https://twitter.com">
                      </div>
                      <div class="col-sm-6">
                        <label class="form-label" for="githubControlInput">Github</label>
                        <input class="form-control" id="githubControlInput" type="url" placeholder="https:/github.com">
                      </div>
                      <div class="col-12"> 
                        <div class="input-group">
                          <input class="form-control" id="inputGroupFile04" type="file" aria-describedby="inputGroupFileAddon04" aria-label="Upload">
                          <button class="btn btn-outline-secondary" id="inputGroupFileAddon04" type="button">Submit</button>
                        </div>
                      </div>
                      <div class="col-12">
                        <select class="form-select" aria-label="Default select example">
                          <option selected="">Positions</option>
                          <option value="1">Web Designer</option>
                          <option value="2">Software Engineer</option>
                          <option value="3">UI/UX Designer </option>
                          <option value="3">Web Developer</option>
                        </select>
                      </div>
                      <div class="col-12"> 
                        <label class="form-label" for="quationsTextarea">Why do you want to take this position?</label>
                        <textarea class="form-control" id="quationsTextarea" rows="2"></textarea>
                      </div>
                    </div>
                  </div>
                  <div>
                    <div class="text-end pt-3">
                      <button class="btn btn-secondary" id="prevBtn" type="button" onclick="nextPrev(-1)">Previous</button>
                      <button class="btn btn-primary" id="nextBtn" type="button" onclick="nextPrev(1)">Next</button>
                    </div>
                  </div>
                  <!-- Circles which indicates the steps of the form:-->
                  <div class="text-center"><span class="step"></span><span class="step"></span><span class="step"></span><span class="step"></span></div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>

@endsection


@push('js')

<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('.contactos').select2({
            placeholder: "{{ __('Seleciona Alunos Irmãos') }}",
            allowClear: true
        });
    });
    $(document).ready(function() {
        $('.contactos1').select2({
            placeholder: "{{ __('Seleciona Actividades Co-Curricular') }}",
            allowClear: true
        });
    });

    $(document).ready(function() {
        $('.contactos2').select2({
            placeholder: "{{ __('Seleciona Relacao Pessoa com Aluno') }}",
            allowClear: true
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

    $("#msform").validate({
        // Adicionar regras para cada campo
        rules: {
            plano: {
                required: true
            },
            turma: {
                required: true
            },
            classe: {
                required: true
            },
            endereco: {
                required: true,
                minlength: 2,
            },
            numero_documento: {
                required: true
            },
            tipo_documento: {
                required: true
            },
            provincia: {
                required: true
            },
            data_nascimento: {
                required: true,
            },
            genero: {
                required: true
            },
            nome_casa: {
                minlength: 3,
            },
            nomealuno: {
                required: true,
                minlength: 3,
            },
            imagem: {
                required: true
            }
       
     },
        submitHandler: function(form) {
            $.ajax({
                type: "POST",
                url: "{{route('matricula.update')}}",
                data: $(form).serialize(), // Corrigido para usar `form` em vez de `this`

                beforeSend: function () {
                    // Desabilita o botão de envio e altera o ícone para mostrar que está autenticando
                    $('#botao_salvar').attr('disabled', true);
                    $('#icon_enviar').removeClass('ri-arrow-right-line').addClass('spinner-border ri-loader-2-line');
                    $('#botao_texto').text('Matriculando Aluno...');
                },

                success: function(response) {
                    // Habilita o botão e retorna o ícone original
                    $('#botao_salvar').attr('disabled', false);
                    $('#icon_enviar').removeClass('spinner-border ri-loader-2-line').addClass('ri-arrow-right-line');
                    $('#botao_texto').text('Matricular Aluno');

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
                    $('#botao_texto').text('Matricular Aluno');

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

<script type="text/javascript">
     
     navigator.mediaDevices.getUserMedia({
           video: true
         })
 
         .then(function (stream) {
           
           var video = document.querySelector('#video');
           
           video.srcObject = stream;
           
           video.play();
         })
 
 
         document.querySelector('#capture').addEventListener('click', function () {
            const canvas = document.querySelector("#canvas");
            const video = document.querySelector("#video");

            // Ajustar tamanho do canvas para o vídeo
            canvas.height = video.videoHeight;
            canvas.width = video.videoWidth;

            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0);

            // Converter para base64
            const base64Image = canvas.toDataURL('image/png');

            // Enviar a imagem para o servidor via AJAX ou salvar no formulário
            $('<input>').attr({
                type: 'hidden',
                name: 'imagem',
                value: base64Image
            }).appendTo('#msform');

        });

        function b64ToUint8Array(b64Image) {
             var img = atob(b64Image.split(',')[1]);
             var img_buffer = [];
             var i = 0;
             while (i < img.length) {
                 img_buffer.push(img.charCodeAt(i));
                 i++;
             }
             return new Uint8Array(img_buffer);
        }
 
</script>

<script>
    function getTurmas() {
        const classeId = document.getElementById('classe').value;
        const escolaId = @json(Auth::user()->escola_id);
        const turmaSelect = document.getElementById('turma');

        if (!classeId) {
            turmaSelect.innerHTML = '<option value="">Escolhe o Regime...</option>';
            return;
        }

        // Requisição AJAX para obter as turmas
        fetch(`/api/turma/${classeId}/${escolaId}`)
            .then(response => response.json())
            .then(data => {
                turmaSelect.innerHTML = '<option value="">Escolhe o Regime...</option>';
                data.forEach(turma => {
                    turmaSelect.innerHTML += `<option value="${turma.id}">${turma.nome} - ${turma.periodo.detalhe}</option>`;
                });
            })
            .catch(error => {
                console.error('Erro ao carregar turmas:', error);
                turmaSelect.innerHTML = '<option value="">Erro ao carregar turmas</option>';
            });
    }
</script>

<script>
function mostrarPlano() {
    // Capturar o valor selecionado
    const plano = document.getElementById("plano").value;

    // Referenciar as divs
    const planoNormalDiv = document.getElementById("planoNormal");
    const planoGoldDiv = document.getElementById("planoGold");

    // Mostrar ou ocultar as divs com base no valor selecionado
    if (plano === "1") {
        planoNormalDiv.style.display = "block";
        planoGoldDiv.style.display = "none";
    } else if (plano === "2") {
        planoGoldDiv.style.display = "block";
        planoNormalDiv.style.display = "none";
    } else {
        // Esconder ambas as divs caso nenhuma opção válida esteja selecionada
        planoNormalDiv.style.display = "none";
        planoGoldDiv.style.display = "none";
    }
}
</script>



@endpush

