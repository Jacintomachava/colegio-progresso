
@extends('layouts.app')

@push('css')


<style>
    /* Estilo geral da tabela */
.table {
    border-collapse: collapse; /* Remove espaços entre células */
    width: auto; /* Faz a tabela ocupar todo o espaço disponível */
    font-size: 11px; /* Define o tamanho padrão do texto */
    table-layout: fixed; /* Controla as larguras das colunas */
}

.input-pequeno {
    width: 100%;
    padding: 2px;
    height: 30px;
    font-size: 12px;
    text-align: center;
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
    max-height: 600px; /* Limita a altura da tabela */
}

</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

@endpush

@section('conteudo')

<div class="container">

  <div class="row" style="margin-left: -12%; width: 125%;">
      <div class="card light-table-bg title-line" >
          <div class="card-header">
              <div class="row">
                <div class="col-sm-8">
                    <h3>Pauta de Exame da {{$matricula->classe->nome}}</h3>
                </div>
                <div class="col-sm-4">
                   <a class="btn btn-primary btn-sm" target="_blank" href="{{route('pautaExamePDF.aluno',['codigo'=>$matricula->aluno->codigo,'ano'=>$matricula->anoAcademico->ano])}}"  >
                        Exportar Pauta de Exame da  {{$matricula->classe->nome}}  em PDF
                    </a>
                </div>
              </div>
          </div>
          <div class="card-block row">
              <div class="col-sm-12 col-lg-12 col-xl-12">
                  <div class="table-responsive custom-scrollbar">
                      <table class="table table-bordered" id="tabelas-notas">
                        <thead>
                            <tr>
                                <th  rowspan="2" style="border: 0.2px solid black;  width: 4%; ">Disciplina</th>
                                <th  rowspan="2" style="border: 0.2px solid black;  width: 3%;">Prov.</th>
                                <th scope="col" rowspan="2" style="border: 0.2px solid black; width: 3%;">NCD</th>
                                <th scope="col" colspan="2" style="border: 0.2px solid black; width: 3%;">Exame</th>
                                <th scope="col" rowspan="2" style="border: 0.2px solid black; width: 3%;">Voto</th>
                                <th scope="col" rowspan="2" style="border: 0.2px solid black; width: 3%;">NF</th>
                            </tr>
                            <tr>
                                <th scope="col"  style="border: 0.2px solid black; width: 3%">1ª ch.</th>
                                <th scope="col"  style="border: 0.2px solid black; width: 3%;">2ª ch.</th>
                            </tr>

                        </thead>

                        <tbody>
                            @php

                                $totalNF = 0;
                                $totalDisciplinas = 0;
                                $mediaFinal = 0;
                                $resultadoFinal = '';

                            @endphp

                            @foreach($pautasExames as $pautasExame)

                                @php
                                    foreach ($pautasExames as $px) {
                                        if ($px->nf > 0) {
                                            $totalNF += $px->nf;
                                            $totalDisciplinas++;
                                        }
                                    }

                                    $mediaFinal = $totalDisciplinas > 0
                                        ? round($totalNF / $totalDisciplinas, 0)
                                        : 0;

                                    $resultadoFinal = $mediaFinal >= 9.5 ? 'Aprovado' : 'Reprovado';
                                @endphp

                                <tr>
                                    <td style="border: 0.2px solid black; width: 0.1%; text-align:left; ">{{ $pautasExame->disciplina->nome }}</td>
                                    <td style="border: 0.2px solid black; width: 0.1%; ">{{ $pautasExame->proveniencia ?? '' }}</td>

                                        <td>{{ $pautasExame->ncd == 0 ? '' : $pautasExame->ncd }}</td>
                                        <td>{{ $pautasExame->primeira_chamada==0 ? '': $pautasExame->primeira_chamada }}</td>
                                        <td>{{ $pautasExame->segunda_chamada==0 ? '':  $pautasExame->segunda_chamada }}</td>
                                        <td>{{ $pautasExame->voto==0 ? '': $pautasExame->voto }}</td>
                                        <td style="text-align: center;">
                                            {{ $pautasExame->nf==0? '': $pautasExame->nf }}
                                        </td>

                                </tr>
                            @endforeach
                                <tr>
                                    <th colspan="5"  style="border: 0.2px solid black; width: 3%">Aproveitamento Pedagógica </th>
                                    <th scope="col"  style="border: 0.2px solid black; width: 3%;">{{$resultadoFinal}}</th>
                                    <th scope="col"  style="border: 0.2px solid black; width: 3%;">{{$mediaFinal==0? '': $mediaFinal}}</th>
                                </tr>
                        </tbody>
                      </table>
                  </div>
              </div>
          </div>
      </div>
  </div>

</div>


@endsection

@push('js')


@endpush
