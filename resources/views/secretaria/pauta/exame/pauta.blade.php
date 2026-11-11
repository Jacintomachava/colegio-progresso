
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
                    <h3>Pauta de Exame da {{$classe->nome}} - Ano: {{$anoAcademico->ano}} </h3>
                </div>
                <div class="col-sm-4">
                   <a class="btn btn-primary btn-sm" target="_blank" href="{{route('pautaPDF.exame',['classe'=>$classe->id, 'ano' => $anoAcademico->ano ])}}"  >
                        Exportar Pauta de Exame da {{$classe->nome}} - {{$anoAcademico->ano}}   em PDF
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
                                <th  rowspan="3" style="border: 0.2px solid black;  width: 3%;" >Nº</th>
                                <th  rowspan="3" style="border: 0.2px solid black;  width: 4%; ">Nome</th>
                                <th  rowspan="3" style="border: 0.2px solid black;  width: 3%;">G</th>
                                <th  rowspan="3" style="border: 0.2px solid black;  width: 3%;">Prov.</th>

                                {{-- Cabeçalho das disciplinas --}}
                                @foreach ($disciplinasComExame as $d)
                                    <th colspan="5" style="border: 0.2px solid black;  ">{{ $d->disciplina->nome }}</th>
                                @endforeach

                                <th rowspan="3" style="border: 0.2px solid black;  width: 3%;">M F</th>
                                <th rowspan="3" style="border: 0.2px solid black; width: 5.5%; ">Resultado</th>
                            </tr>
                            <tr>
                                @foreach ($disciplinasComExame as $d)
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; width: 3%;">NCD</th>
                                    <th scope="col" colspan="2" style="border: 0.2px solid black; width: 3%;">Exame</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; width: 3%;">Voto</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; width: 3%;">NF</th>
                                @endforeach
                            </tr>
                            <tr>
                                @foreach ($disciplinasComExame as $d)
                                    <th scope="col"  style="border: 0.2px solid black; width: 3%">1ª ch.</th>
                                    <th scope="col"  style="border: 0.2px solid black; width: 3%;">2ª ch.</th>
                                @endforeach
                            </tr>

                        </thead>

                        <tbody>
                            @foreach($alunos as $mat)
                                @php
                                    $aluno = $mat->aluno;

                                    // PX base do aluno (qualquer disciplina)
                                    $pxBase = $pautasExame->firstWhere('aluno_id', $aluno->id);

                                    // Obter todas notas final do aluno
                                    $nfs = [];
                                    

                                    foreach ($disciplinasComExame as $disc) {
                                        $px = $pautasExame->where('aluno_id', $aluno->id)
                                                        ->where('disciplina_id', $disc->disciplina_id)
                                                        ->first();

                                        $nfs[] = $px->nf ?? 0;
                                    }

                                    // Média final = soma das NFs / nº disciplinas
                                    $mediaFinal = count($nfs) > 0 ? round(array_sum($nfs) / count($nfs), 0) : 0;

                                    $resultado = $mediaFinal >= 9.5 ? 'Aprovado' : 'Reprovado';
                                @endphp

                                <tr>
                                    <td style="border: 0.2px solid black; width: 0.1%; ">{{ $pxBase?->numero ?? '' }}</td>
                                    <td style="border: 0.2px solid black; width: 0.1%; text-align:left; ">{{ $aluno->pessoa->nome }}</td>
                                    <td style="border: 0.2px solid black; width: 0.1%; ">{{ substr($aluno->pessoa->genero, 0, 1) }}</td>
                                    <td style="border: 0.2px solid black; width: 0.1%; ">{{ $pxBase?->proveniencia ?? '' }}</td>

                                    {{-- Notas por disciplina --}}
                                    @foreach($disciplinasComExame as $disc)
                                        @php
                                            $px = $pautasExame->where('aluno_id', $aluno->id)
                                                            ->where('disciplina_id', $disc->disciplina_id)
                                                            ->first();
                                        @endphp
                                        <td>{{ $px->ncd == 0 ? '' : $px->ncd }}</td>
                                        <td>{{ $px->primeira_chamada==0 ? '': $px->primeira_chamada }}</td>
                                        <td>{{ $px->segunda_chamada==0 ? '': $px->segunda_chamada }}</td>
                                        <td>{{ $px->voto==0 ? '':$px->voto }}</td>
                                        <td style="text-align: center;">
                                            {{ $px->nf==0? '':$px->nf }}
                                        </td>
                                    @endforeach

                                    <td style="font-weight: bold; text-align:center;">
                                        {{ $mediaFinal==0? '': $mediaFinal }}
                                    </td>

                                    <td style="font-weight: bold; color: {{ $resultado == 'Aprovado' ? 'green' : 'red' }}">
                                        {{ $mediaFinal==0? '': $resultado }}
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


@endsection

@push('js')


@endpush
