
@extends('layouts.app')

@push('css')

<style>

/* Estilo geral da tabela */
.table {
    border-collapse: collapse; /* Remove espaços entre células */
    width: 100%; /* Faz a tabela ocupar todo o espaço disponível */
    font-size: 11px; /* Define o tamanho padrão do texto */
    table-layout: fixed; /* Controla as larguras das colunas */
}

/* Quando a tela for menor que 768px (mobile/tablet) */
@media (max-width: 768px) {
    .table {
        width: 250%; /* Aumenta a largura para 120% do container */
        font-size: 11px; /* Opcional: aumenta o tamanho do texto para melhor leitura */
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
    max-height: 500px; /* Limita a altura da tabela */
}

</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

@endpush

@section('conteudo')

<div class="row">


    
    <form action="{{route('notas.buscar')}}" method="POST">
        
      <div class="row">
        <div class="col-md-7">
            @csrf

            <input class="form-control" type="hidden" name="classe" value="{{$minhaClasse->id}}" >

            <div class="form-group">
                <select name="disciplina" id="disciplina" class="form-control" required>
                    @if ($disciplina==null)
                        <option  value="">Seleciona a Disciplina</option>
                        @foreach($turmaProfessorDisciplinas as $turmaProfessorDisciplina)
                            <option value="{{ $turmaProfessorDisciplina->disciplina_id }}"  >
                                {{ $turmaProfessorDisciplina->disciplina->nome }} 
                            </option>
                        @endforeach
                    @else
                        @foreach($turmaProfessorDisciplinas as $turmaProfessorDisciplina)
                            <option value="{{ $turmaProfessorDisciplina->disciplina_id }}"  @if ($turmaProfessorDisciplina->disciplina_id ==$disciplina ) selected @endif >
                                {{ $turmaProfessorDisciplina->disciplina->nome }} 
                            </option>
                        @endforeach
                    @endif
                    
                </select>
            </div>
        </div> 
        <div class="col-md-2">
          <div class="form-group">  
            <button type="submit" class="btn btn-success">
                <i class="fa fa-search"></i> Buscar
            </button>
          </div>  
        </div>
        <div class="col-md-3">
            {{--

                <div class="form-group"> 
                    <a class="btn btn-primary" data-bs-toggle="modal" data-bs-target=".bd-example-modal-lg">
                        <i class="fa fa-edit"></i> Lancar Notas
                    </a> 
                </div>  
                
                --}}
          
        </div>
      </div>      
    </form>

    <br>

    <div class="col-sm-12">
        <div class="card light-table-bg title-line">
            <div class="card-header">
                <h3>Notas de Frequência da {{ $minhaClasse->nome }} 
                     @if ($disciplina!=null) 
                        @foreach($turmaProfessorDisciplinas as $turmaProfessorDisciplina)
                             @if ($turmaProfessorDisciplina->disciplina_id ==$disciplina ) 
                                - {{ $turmaProfessorDisciplina->disciplina->nome }} 
                             @endif 
                        @endforeach
                     @endif
                     
                     Ano {{$anoAtivo->ano}}
                

                @if($disciplina!=null)
                    <a class="btn btn-primary btn-sm activate-btn" target="_blank" href="{{route('deck.professor',['turma'=>$minhaClasse->id,'disciplina'=>$disciplina])}}" >
                        Exportar Deck PDF
                    </a>
                @endif

                </h3>
            </div>
            <div class="card-block row">
                <div class="col-sm-12 col-lg-12 col-xl-12">
                    <div class="table-responsive signal-table custom-scrollbar">
                        <table class="table table-hover" id="tabela-notas">
                            <thead>
                                <tr>
                                    <th scope="col" style="border: 0.2px solid black; width: 2%;"></th>
                                    <th scope="col" colspan="1" style="border: 0.2px solid black; width: 15%;"></th>
                                    <th scope="col" style="border: 0.2px solid black; width: 2%;"></th>

                                    <th scope="col" colspan="7" style="border: 0.2px solid black; text-align: center;">1º Trimestre</th>
                                    <th scope="col" colspan="7" style="border: 0.2px solid black; text-align: center;">2º Trimestre</th>
                                    <th scope="col" colspan="7" style="border: 0.2px solid black; text-align: center;">3º Trimestre</th>

                                    <th scope="col" rowspan="3" style="border: 0.2px solid black; text-align: center;">M.<br> Anual</th>
                                </tr>

                                <tr>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black;">Nr.</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black;">Nome de Alunos</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black;">G</th>

                                    <!-- 1º Trimestre -->
                                    <th scope="col" colspan="4" style="border: 0.2px solid black;">ACS</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black;">M.<br>ACS</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black;">AT</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black;">MT</th>

                                    <!-- 2º Trimestre -->
                                    <th scope="col" colspan="4" style="border: 0.2px solid black;">ACS</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black;">M.<br>ACS</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black;">AT</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black;">MT</th>

                                    <!-- 3º Trimestre -->
                                    <th scope="col" colspan="4" style="border: 0.2px solid black;">ACS</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black;">M.,br>ACS</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black;">AT</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black;">MT</th>
                                </tr>

                                <tr>
                                    <!-- Testes 1º -->
                                    @for ($i = 1; $i <= 4; $i++)
                                        <th scope="col" style="border: 0.2px solid black;">{{ $i }}ª</th>
                                    @endfor

                                    <!-- Testes 2º -->
                                    @for ($i = 1; $i <= 4; $i++)
                                        <th scope="col" style="border: 0.2px solid black;">{{ $i }}ª</th>
                                    @endfor

                                    <!-- Testes 3º -->
                                    @for ($i = 1; $i <= 4; $i++)
                                        <th scope="col" style="border: 0.2px solid black;">{{ $i }}ª</th>
                                    @endfor
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($pautas as $aluno)
                                    <tr>
                                        <td style="border: 0.2px solid black; text-align: center;">{{ $aluno->numero }}</td>
                                        <td style="border: 0.2px solid black; text-align: left;">{{ $aluno->aluno->pessoa->nome }}</td>
                                        <td style="border: 0.2px solid black;">{{ substr($aluno->aluno->pessoa->genero, 0, 1) }}</td>

                                        @php
                                            $mediaFinal = 0;
                                        @endphp

                                        @foreach(range(1, 3) as $trimestre)

                                            @php
                                                // Somente os 4 testes (ACS)
                                                $testes = [
                                                    $aluno->{'teste1Tr' . $trimestre},
                                                    $aluno->{'teste2Tr' . $trimestre},
                                                    $aluno->{'teste3Tr' . $trimestre},
                                                    $aluno->{'teste4Tr' . $trimestre},
                                                ];

                                                $validos = array_filter($testes, fn($t) => $t > 0);

                                                $mediaTestes = count($validos) > 0
                                                    ? array_sum($validos) / count($validos)
                                                    : 0;

                                                $notaFinalTrimestre = round((2 * $mediaTestes + $aluno->{'apTr' . $trimestre}) / 3);

                                                $mediaFinal += $notaFinalTrimestre;
                                            @endphp

                                            <!-- ACS (4 testes) -->
                                            @foreach($testes as $teste)
                                                <td style="border: 0.2px solid black;">
                                                    {{ $teste > 0 ? $teste : '' }}
                                                </td>
                                            @endforeach

                                            <!-- M.ACS -->
                                            <td style="border: 0.2px solid black;">
                                                {{ $mediaTestes > 0 ? round($mediaTestes) : '' }}
                                            </td>

                                            <!-- AT -->
                                            <td style="border: 0.2px solid black;">
                                                {{ $aluno->{'apTr' . $trimestre} > 0 ? $aluno->{'apTr' . $trimestre} : '' }}
                                            </td>

                                            <!-- MT -->
                                            <td style="border: 0.2px solid black;">
                                                @if($aluno->{'apTr' . $trimestre} > 0)
                                                    {{ $notaFinalTrimestre }}
                                                @endif
                                            </td>

                                        @endforeach

                                        <!-- Média Anual -->
                                        <td style="border: 0.2px solid black;">
                                            @if($aluno->apTr1 > 0 && $aluno->apTr2 > 0 && $aluno->apTr3 > 0)
                                                {{ round($mediaFinal / 3) }}
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


<div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModal" aria-hidden="true">
    <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form  id="form">  
        <div class="modal-header">
            <h4 class="modal-title" id="myExtraLargeModal">Lancar Notas - {{ $minhaClasse->nome }} </h4>
            <button class="btn-close py-0" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body dark-modal">

            @csrf

            <input class="form-control"  type="hidden" name="classe" value="{{ $minhaClasse->id }}"  placeholder="Ano" >
            <input class="form-control"  type="hidden" name="ano" value="{{$anoAtivo->id}}"  placeholder="Ano" >

            <div class="row">

                <div class="col-4">
                   <label class="form-label" for="passwordwizard">Trimestre<span class="txt-danger">*</span></label>
                    <select class="form-select"  name="trimestre" >
                        <option value="1" @if($anoAtivo->trimestre==1) selected @endif > 1º Trimestre</option>
                        <option value="2" @if($anoAtivo->trimestre==2) selected @endif > 2º Trimestre</option>
                        <option value="3" @if($anoAtivo->trimestre==3) selected @endif > 3º Trimestre</option>
                    </select>
                </div>

                <div class="col-4">
                   <label class="form-label" for="passwordwizard">Teste<span class="txt-danger">*</span></label>
                    <select class="form-select"  name="teste" >
                        <option value="">Seleciona o Teste</option>
                        <option value="teste1Tr"> Teste 1</option>
                        <option value="teste2Tr"> Teste 2</option>
                        <option value="teste3Tr"> Teste 3</option>
                        <option value="teste4Tr"> Teste 4</option>
                        <option value="mmTr"> MM</option>
                        <option value="apTr"> AT </option>
                    </select>
                </div>

                <div class="col-4">
                  <label class="form-label" for="passwordwizard">Disciplina<span class="txt-danger">*</span></label>
                    <select class="form-select"  name="disciplina">
                        <option value="">Seleciona o Disciplina</option>
                        @foreach($turmaProfessorDisciplinas as $turmaProfessorDisciplina)
                        <option value="{{$turmaProfessorDisciplina->disciplina_id}}">{{$turmaProfessorDisciplina->disciplina->nome}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-8">
                  <label class="form-label" for="passwordwizard">Aluno<span class="txt-danger">*</span></label>
                    <select class="form-select"  name="aluno" id="aluno">
                        <option value="">Seleciona o Aluno</option>
                        @foreach($alunosMatriculados as $alunosMatriculado)
                        <option value="{{$alunosMatriculado->aluno->id}}">{{$alunosMatriculado->aluno->pessoa->nome}} - {{$alunosMatriculado->aluno->codigo}} </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-4">
                   <label class="form-label" for="passwordwizard">Notas<span class="txt-danger">*</span></label>
                   <input class="form-control"  type="text" name="nota" placeholder="Nota" >
                </div>
            </div>

        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
            <button class="btn btn-primary" id="botao_salvar" type="submit" >
                <span id="botao_texto">{{__('Lancar Notas')}}</span>
                <i id="icon_enviar" class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
            </button>
        </div>
      </form>  
    </div>
    </div>
</div>


@endsection

@push('js')

<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const alunoSelect = new Choices('#aluno', {
            searchPlaceholderValue: "Digite para pesquisar...",
            shouldSort: false,  // Mantém a ordem dos alunos no dropdown
        });
    });
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
    function exportarExcel() {
      // Obter a tabela HTML
      const tabela = document.getElementById('tabela-notas');
      
      // Converter tabela para uma worksheet do SheetJS
      const worksheet = XLSX.utils.table_to_sheet(tabela);

      // Criar um novo workbook (arquivo Excel)
      const workbook = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(workbook, worksheet, 'Notas');

      // Exportar para um arquivo .xlsx
      XLSX.writeFile(workbook, 'notas.xlsx');
    }
</script>

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
            trimestre: {
                required: true
            },
            disciplina: {
                required: true
            },
            aluno: {
                required: true
            },
            teste: {
                required: true
            },
            nota: {
                required: true,
                min: 0,
                max: 20
            }
        },
        submitHandler: function(form) {
            $.ajax({
                type: "POST",
                url: "{{route('notas.store')}}",
                data: $(form).serialize(), // Corrigido para usar `form` em vez de `this`

                beforeSend: function () {
                    // Desabilita o botão de envio e altera o ícone para mostrar que está autenticando
                    $('#botao_salvar').attr('disabled', true);
                    $('#icon_enviar').removeClass('ri-arrow-right-line').addClass('spinner-border ri-loader-2-line');
                    $('#botao_texto').text('Lancar Notas...');
                },

                success: function(response) {
                    // Habilita o botão e retorna o ícone original
                    $('#botao_salvar').attr('disabled', false);
                    $('#icon_enviar').removeClass('spinner-border ri-loader-2-line').addClass('ri-arrow-right-line');
                    $('#botao_texto').text('Lancar Notas');

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
                    $('#botao_texto').text('Lancar Notas');

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


@endpush
