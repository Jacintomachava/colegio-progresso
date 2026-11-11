
@extends('layouts.app')

@push('css')

<style>

/* Estilo geral da tabela */
.table {
    border-collapse: collapse; /* Remove espaços entre células */
    width: 100%; /* Faz a tabela ocupar todo o espaço disponível */
    font-size: 10px; /* Define o tamanho padrão do texto */
    table-layout: fixed; /* Controla as larguras das colunas */
}

/* Quando a tela for menor que 768px (mobile/tablet) */
@media (max-width: 768px) {
    .table {
        width: 250%; /* Aumenta a largura para 120% do container */
        font-size: 12px; /* Opcional: aumenta o tamanho do texto para melhor leitura */
    }

    /* Para scroll horizontal se a tabela ficar maior que a tela */
    .table-container {
        overflow-x: auto;
    }
}

/* Estilo para células */
.table th, .table td {
    border: 1px solid black; /* Bordas finas */
    padding: 5px; /* Espaçamento interno */
    text-align: center; /* Centraliza o texto */
    vertical-align: middle; /* Centraliza verticalmente */
    overflow: hidden; /* Impede que conteúdo ultrapasse a célula */
    text-overflow: ellipsis; /* Adiciona "..." ao texto cortado */
    white-space: nowrap; /* Evita quebra de linha */
}

/* Ajuste para cabeçalhos */
.table th {
    background-color: #f8f9fa; /* Fundo claro para destaque */
    font-weight: bold; /* Títulos em negrito */
}

/* Alternância de cores nas linhas */
.table tbody tr:nth-child(even) {
    background-color: #f2f2f2; /* Fundo alternado */
}

/* Rolagem horizontal (caso necessário) */
.custom-scrollbar {
    overflow-x: auto; /* Habilita rolagem horizontal */
    max-height: 400px; /* Limita a altura da tabela */
}

</style>

@endpush

@section('conteudo')

<div class="row">

    @role('Aluno')


      @if(count($mensalidades) > 0)

        <div class="col-xl-12 box-col-12">
          <div class="card title-line upgrade-card overflow-hidden">
            <div class="row align-items-end"> 
              <div class="col-sm-8 col-11"> 
                <div class="card-header"> 
                  <h2>Estimado Estudante: <i>{{$aluno->pessoa->nome}} </i><span class="txt-primary">! </span></h2>
                  <p class="mt-2 f-light">
                    Para Poder Visualizar a sua Pauta,
                    Queira por favor regularizar a sua situacao Financeira(Pagamento de Mensalidade)
                    Clique para baixar a fatura de Propinas 
                  </p>
                  <a class="btn btn-primary btn-hover-effect btn-sm f-w-500" target="_blank" href="{{route('propina.factura',['alunoCodigo'=>$aluno->codigo])}}">Imprimir Factura
                    <svg class="svg-sprite">
                      <use href="{{ URL('/assets/svg/icon-sprite.svg#logout') }}"> </use>
                    </svg></a>
                </div>
              </div>
            </div>
            <div class="cartoon-image"> <img class="img-fluid" src="{{ URL('/assets/images/dashboard/welcome.png') }}" alt="vector"></div><img class="img-fluid pattern-image" src="{{ URL('/assets/images/dashboard/bg-1.png') }}" alt="vector pattern">
          </div>
        </div>

      @else

       <form action="{{route('pauta.buscar')}}" method="POST">
          
        <div class="row">
          <div class="col-md-10">
              @csrf

              <input class="form-control" type="hidden" name="aluno" value="{{$aluno->id}}" >

              <div class="form-group">
                  <label for="contactos" class="form-label">{{ __('Seleciona o ano para ver a Pauta') }} </label>
                  <select name="ano" id="ano" class="form-control" required>
                      @foreach($anosAcademicos as $ano)
                          <option value="{{ $ano->id }}" @if(optional($anoAtivo)->id == $ano->id) selected @endif>
                              {{ $ano->nome }}
                          </option>
                      @endforeach
                  </select>
              </div>
          </div> 
          <div class="col-md-2">
            <br>
            <div class="form-group"> 
              <button type="submit" class="btn btn-success">
                  <i class="fa fa-search"></i> Buscar 
              </button>
            </div>  
          </div>
        </div>      
      </form>

      <br>

      <div class="col-sm-12">
        <div class="card light-table-bg title-line">
            <div class="card-header row">
                <div class="col-md-8">
                    <h3>@if($matricula!=null) Notas de Frequência: {{$matricula->classe->nome}} - {{$matricula->anoAcademico->ano}} -- 
                        <a class="btn btn-primary" target="_blank" href="{{route('pauta.alunoPDF',['codigo'=>$aluno->codigo,'ano'=>$matricula->anoAcademico->ano])}}">
                            <i class="fa fa-print"></i> Imprimir Pauta
                        </a>
                        @endif            
                    </h3>
                </div>
                <div class="col-md-4">
                    <a class="btn btn-warning" href="{{route('pautaExame.aluno',['codigo'=>$aluno->codigo,'ano'=>$matricula->anoAcademico->ano])}}">
                            <i class="fa fa-print"></i> Pauta de Exame da {{$matricula->classe->nome}}
                    </a>
                </div>
            </div>
            @php
                $totalDisciplinas = 0;  
                $totalNotasFinaisDisciplina = 0;
                $pautaFinal = 0;
            @endphp
            <div class="card-block row">
                <div class="col-sm-12 col-lg-12 col-xl-12">
                    <div class="table-responsive signal-table custom-scrollbar">
                        <table class="table table-hover" id="tabela-notas">
                            <thead>
                                <tr>
                                    <th scope="col" colspan="1" style="border: 0.2px solid black; width: 4.5%;"></th>
                                    <th scope="col" style="border: 0.2px solid black; width: 1%; "></th>
                                    <th scope="col" colspan="9" style="border: 0.2px solid black; text-align: center; width: 10%; ">1º Trimestre</th>
                                    <th scope="col" colspan="9" style="border: 0.2px solid black; text-align: center;  width: 10%; ">2º Trimestre</th>
                                    <th scope="col" colspan="9" style="border: 0.2px solid black; text-align: center;  width: 10%; ">3º Trimestre</th>
                                    <th scope="col" rowspan="3" style="border: 0.2px solid black; text-align: center; width: 2%;  ">M. Anual</th>
                                </tr>
                                <tr>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black;  ">Disciplinas</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; ">T</th>

                                    <!-- 1º Trimestre -->
                                    <th scope="col" colspan="4" style="border: 0.2px solid black;">ACS</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; " hidden>MM</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; ">M.<br>ACS</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black;">AT</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; ">MT</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; ">Cop.</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; ">Sin.</th>

                                    <!-- 2º Trimestre -->
                                    <th scope="col" colspan="4" style="border: 0.2px solid black;">ACS</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; " hidden>MM</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; ">M.<br>ACS</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black;">AT</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; ">MT</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; ">Cop.</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; ">Sin.</th>

                                    <!-- 3º Trimestre -->
                                    <th scope="col" colspan="4" style="border: 0.2px solid black;">ACS</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; " hidden>MM</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; ">M.<br>ACS</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black;">AT</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; ">MT</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; ">Cop.</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; ">Sin.</th>
                                </tr>
                                <tr>

                                    <!-- Testes -->
                                    @for ($i = 1; $i <= 4; $i++)
                                        <th scope="col" style="border: 0.2px solid black;">{{ $i }}ª</th>
                                    @endfor

                                    @for ($i = 1; $i <= 4; $i++)
                                        <th scope="col" style="border: 0.2px solid black;">{{ $i }}ª</th>
                                    @endfor

                                    @for ($i = 1; $i <= 4; $i++)
                                        <th scope="col" style="border: 0.2px solid black;">{{ $i }}ª</th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pautas as $aluno)
                                    <tr>
                                        <td style="border: 0.2px solid black; text-align: left;">{{ $aluno->disciplina->nome }}</td>
                                        <td style="border: 0.2px solid black; ">
                                          @if($aluno->disciplina->co_curricular==1)
                                            CC
                                          @else  
                                            C
                                          @endif
                                        </td>

                                        @php
                                        
                                           $mediaFinal = 0; 
                                           $pautaFinal = $anoAtivo->pauta_finalizada;

                                        @endphp

                                        <!-- Trimestres -->
                                        @foreach(range(1, 3) as $trimestre)
                                            @php
                                                // Coleta os testes do trimestre
                                                $testes = [
                                                    $aluno->{'teste1Tr' . $trimestre},
                                                    $aluno->{'teste2Tr' . $trimestre},
                                                    $aluno->{'teste3Tr' . $trimestre},
                                                    $aluno->{'teste4Tr' . $trimestre},
                                                    //$aluno->{'mmTr' . $trimestre},
                                                ];

                                                // Filtra os testes válidos
                                                $testesValidos = array_filter($testes, fn($t) => $t > 0);

                                                // Média trimestral (evitando divisão por zero)
                                                $mediaTestes = count($testesValidos) > 0
                                                    ? array_sum($testesValidos) / count($testesValidos)
                                                    : 0;

                                                // Nota final do trimestre (MT)
                                                $notaFinalTrimestre = (2*$mediaTestes+$aluno->{'apTr' . $trimestre})/3 ?? 0;
                                                $mediaFinal = $mediaFinal + $notaFinalTrimestre;
                                            @endphp

                                            <!-- Exibição das notas -->
                                            @foreach($testes as $teste)
                                                <td  @if($loop->iteration==5) style="border: 0.2px solid black; " @else   @endif >{{ $teste > 0 ? $teste : '' }} </td>
                                            @endforeach

                                            <!-- Cálculos -->
                                            <td style="border: 0.2px solid black; ">{{ $mediaTestes > 0 ? number_format($mediaTestes, 2) : '' }}</td>
                                            <td style="border: 0.2px solid black;">{{ $aluno->{'apTr' . $trimestre} > 0 ? $aluno->{'apTr' . $trimestre} : '' }}</td>
                                            <td style="border: 0.2px solid black; ">@if($aluno->{'apTr' . $trimestre}>0) {{ $notaFinalTrimestre > 0 ? round($notaFinalTrimestre) : '' }} @else  @endif </td>
                                            <td style="border: 0.2px solid black; ">@if($aluno->{'cop'. $trimestre}!=0 && $aluno->{'cop'. $trimestre}!=null ) {{ $aluno->{'cop'. $trimestre} }} @else @endif </td>
                                            <td style="border: 0.2px solid black; ">@if($aluno->{'sin'. $trimestre}!=0 && $aluno->{'sin'. $trimestre}!=null ) {{ $aluno->{'sin'. $trimestre} }} @else @endif </td>
                                        @endforeach


                                        <td style="border: 0.2px solid black;  ">
                                          @if($aluno->apTr1 > 0 && $aluno->apTr2 > 0 && $aluno->apTr3 > 0) 
                                            {{ round($mediaFinal > 0 ? $mediaFinal/3 : '') }}
                                            @else  

                                          @endif
                                          @php
                                              if($aluno->apTr3 > 0 && $aluno->disciplina->co_curricular == 0) {
                                                  $totalDisciplinas++;  
                                                  $totalNotasFinaisDisciplina += round($mediaFinal / 3);
                                              }
                                          @endphp
                                        </td>
                                    </tr>
                                @endforeach
                                    <tr>
                                        <th scope="col" colspan="25" style="border: 0.2px solid black; width: 4.5%;">Aproveitamento Pedagogico</th>
                                        <th scope="col" colspan="4" style="border: 0.2px solid black; text-align: center; width: 10%;">
                                          @php
                                              $aproveitamento = '';
                                              if ($totalDisciplinas > 0 && $pautaFinal==1) {
                                                  if($matricula->classe->tem_exame==0){
                                                    $aproveitamento = ($totalNotasFinaisDisciplina / $totalDisciplinas) > 9.5 ? 'Progride' : 'Não Progride';
                                                  }else{
                                                    $aproveitamento = ($totalNotasFinaisDisciplina / $totalDisciplinas) > 9.5 ? 'Admitido' : 'Não Admitido';
                                                  }
                                                  
                                              }
                                          @endphp
                                          {{ $aproveitamento }}
                                        </th>
                                        <th scope="col" rowspan="1" style="border: 0.2px solid black; text-align: center; width: 2%; ">
                                            @if($totalDisciplinas > 0 && $pautaFinal==1) 
                                                {{ round($totalNotasFinaisDisciplina / $totalDisciplinas)  }} 
                                            @endif
                                            
                                        </th>
                                    </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
       </div>

      @endif  
      
     
    @endrole  
    
</div>



@endsection

@push('js')

@endpush
