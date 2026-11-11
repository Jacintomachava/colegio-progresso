
@extends('layouts.app')

@push('css')
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">

<style>

/* Estilo geral da tabela */
.table {
    border-collapse: collapse; /* Remove espaços entre células */
    width: auto; /* Faz a tabela ocupar todo o espaço disponível */
    font-size: 11px; /* Define o tamanho padrão do texto */
    table-layout: fixed; /* Controla as larguras das colunas */
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

</head>

@endpush

@section('conteudo')

<div class="container">

   
  <div class="row" style="margin-left: -12%; width: 125%;">
      <div class="card light-table-bg title-line" >
          <div class="card-header">
              <div class="row">
                <div class="col-sm-4">
                    <h3>Pauta da {{$minhaClasse->nome}} - Ano: {{$anoAtivo->ano}}</h3>
                </div>
                <div class="col-sm-4">
                    
                </div>
                <div class="col-sm-4">
                    @if($minhaClasse->tem_exame==1)
                        <a class="btn btn-warning btn-sm"  href="{{route('pauta.exame',['classe'=>$minhaClasse->id,'ano'=>$anoAtivo->ano])}}" >
                            Pauta de Exame da  {{$minhaClasse->nome}}
                        </a>
                    @endif
                   <a class="btn btn-primary btn-sm" target="_blank" href="{{route('pautaPDF.turma',['turma'=>$minhaClasse->id,'ano'=>$anoAtivo->ano])}}" >
                        Exportar Pauta da {{$minhaClasse->nome}} em PDF
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
                                  <th rowspan="2" style="width: 6%;  border: 0.2px solid black; ">N°</th>
                                  <th rowspan="2" style="width: 12%;  border: 0.2px solid black;">Nome</th>
                                  <th rowspan="2" style="width: 2%;  border: 0.2px solid black; ">G</th>
                                  @foreach ($disciplinas as $disciplina)
                                      <th colspan="4" style="border: 0.2px solid black; ">{{ $disciplina['nome'] }}</th>
                                  @endforeach

                                  {{-- Novos campos antes de MG --}}
                                  <th colspan="3" style="border: 0.2px solid black;">Comportamento</th>
                                  <th colspan="3" style="border: 0.2px solid black;">Sinalética</th>

                                  <th rowspan="2" style="border: 0.2px solid black; ">MG</th>
                                  <th rowspan="2" style="border: 0.2px solid black; ">Resultado Final</th>
                              </tr>
                              <tr>
                                  @foreach ($disciplinas as $disciplina)
                                      <th style="border: 0.2px solid black;">1º T</th>
                                      <th style="border: 0.2px solid black;">2º T</th>
                                      <th style="border: 0.2px solid black;">3º T</th>
                                      <th style="border: 0.2px solid black; ">M</th>
                                  @endforeach

                                       {{-- Cabeçalho dos novos campos --}}
                                       <th style="border: 0.2px solid black;">1º T</th>
                                       <th style="border: 0.2px solid black;">2º T</th>
                                       <th style="border: 0.2px solid black;">3º T</th>

                                       <th style="border: 0.2px solid black;">1º T</th>
                                       <th style="border: 0.2px solid black;">2º T</th>
                                       <th style="border: 0.2px solid black;">3º T</th>
                              </tr>
                          </thead>
                          <tbody>
                              @foreach ($dadosTabela as $index => $aluno)
                                  <tr>
                                      <td style="border: 0.2px solid black;">{{ $aluno['numero'] }}</td>
                                      <td style="border: 0.2px solid black; text-align: left;">{{ $aluno['nome'] }}</td>
                                      <td style="border: 0.2px solid black; ">{{ substr($aluno['genero'], 0, 1) }}</td>
                                      @foreach ($aluno['disciplinas'] as $notas)
                                          <td style="border: 0.2px solid black;">{{ $notas['mediaTr1'] > 0 ? round($notas['mediaTr1']) : ''}}</td>
                                          <td style="border: 0.2px solid black;">{{ $notas['mediaTr2'] > 0 ? round($notas['mediaTr2']) : '' }}</td>
                                          <td>{{ $notas['mediaTr3'] > 0 ? round($notas['mediaTr3']) : '' }}</td>
                                          <td style="border: 0.2px solid black; ">{{ $notas['mediaAnual'] > 0 ? round($notas['mediaAnual']) : '' }}</td>
                                      @endforeach

                                        {{-- Comportamento --}}
                                        <td style="border: 0.2px solid black;">{{ $aluno['cop1'] != 0 ? $aluno['cop1'] : '' }}</td>
                                        <td style="border: 0.2px solid black;">{{ $aluno['cop2'] != 0 ? $aluno['cop2'] : '' }}</td>
                                        <td style="border: 0.2px solid black;">{{ $aluno['cop3'] != 0 ? $aluno['cop3'] : '' }}</td>

                                        {{-- Sinalética --}}
                                        <td style="border: 0.2px solid black;">{{ $aluno['sin1'] != 0 ? $aluno['sin1'] : '' }}</td>
                                        <td style="border: 0.2px solid black;">{{ $aluno['sin2'] != 0 ? $aluno['sin2'] : '' }}</td>
                                        <td style="border: 0.2px solid black;">{{ $aluno['sin3'] != 0 ? $aluno['sin3'] : '' }}</td>

                                        {{-- Média Geral e Resultado --}}
                                      <td style="border: 0.2px solid black; ">{{ $aluno['mediaGeral'] > 0 ? round($aluno['mediaGeral']) : '' }}</td>
                                      <td style="border: 0.2px solid black; ">{{ $aluno['mediaGeral'] > 0  ? $aluno['resultadoFinal'] : '' }}</td>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
    function exportarExcel() {
      // Obter a tabela HTML
      const tabela = document.getElementById('tabela-notas');
      
      // Converter tabela para uma worksheet do SheetJS
      const worksheet = XLSX.utils.table_to_sheet(tabela);

      // Criar um novo workbook (arquivo Excel)
      const workbook = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(workbook, worksheet, 'Pauta');

      // Exportar para um arquivo .xlsx
      XLSX.writeFile(workbook, 'pauta.xlsx');
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const activateButtons = document.querySelectorAll('.activate-btn');

        activateButtons.forEach(button => {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');

                Swal.fire({
                    title: 'Tem certeza?',
                    text: "Você deseja Finalizar a Pauta desta Turma? Nao sera possivel lancar notas nessa turma para todas Disciplinas.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sim, Finalizar!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Enviar requisição AJAX
                        fetch(`/escola/finalizar/pauta/${id}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire(
                                    'Ativado!',
                                    data.message,
                                    'success'
                                );
                                // Recarregar a página para atualizar o estado
                                location.reload();
                            } else {
                                Swal.fire(
                                    'Erro!',
                                    data.message,
                                    'error'
                                );
                            }
                        })
                        .catch(() => {
                            Swal.fire(
                                'Erro!',
                                'Ocorreu um erro ao processar sua solicitação.',
                                'error'
                            );
                        });
                    }
                });
            });
        });
    });
</script>

@endpush
