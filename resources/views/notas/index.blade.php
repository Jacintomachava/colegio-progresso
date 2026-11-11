
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
        width: 210%; /* Aumenta a largura para 120% do container */
        font-size: 12px; /* Opcional: aumenta o tamanho do texto para melhor leitura */
    }

    /* Para scroll horizontal se a tabela ficar maior que a tela */
    .table-container {
        overflow-x: auto;
    }
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
    max-height: 650px; /* Limita a altura da tabela */
}

</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

@endpush

@section('conteudo')


<div class="row">
    <div class="col-sm-12">
        <div class="card light-table-bg title-line">


          <form method="GET"
                action="{{ route('lancar.notas', [
                    'classe' => request('classe'),
                    'disciplina' => request('disciplina'),
                    'trimestre' => request('trimestre'),
                    'ano' => request('ano'),
                ]) }}"  id="formNotas">
                <div class="card-header">
                    <h3>
                        <div class="row">
                            <div class="col-md-4">
                                <label for="classe">Classe</label>
                                <select name="classe" id="classe" class="form-select" onchange="updateRoute()">
                                    @foreach ($classes as $classeItem)
                                        <option value="{{ $classeItem->id }}"
                                            {{ request('classe') == $classeItem->id ? 'selected' : '' }}>
                                            {{ $classeItem->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="disciplina">Disciplina</label>
                                <select name="disciplina" id="disciplina" class="form-select" onchange="updateRoute()">
                                    @foreach ($disciplinas as $item)
                                        <option value="{{ $item->disciplina->id }}"
                                            {{ request('disciplina') == $item->disciplina->id ? 'selected' : '' }}>
                                            {{ $item->disciplina->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="trimestre">Trimestre</label>
                                <select name="trimestre" id="trimestre" class="form-select" onchange="updateRoute()">
                                    <option value="1" {{ request('trimestre') == 1 ? 'selected' : '' }}>1º Trimestre</option>
                                    <option value="2" {{ request('trimestre') == 2 ? 'selected' : '' }}>2º Trimestre</option>
                                    <option value="3" {{ request('trimestre') == 3 ? 'selected' : '' }}>3º Trimestre</option>
                                </select>
                            </div>
                        </div>
                    </h3>
                </div>
            </form>

          <form id="form" enctype="multipart/form-data">

                @csrf
                <input type="hidden" name="classe" value="{{ $classe->id }}">
                <input type="hidden" name="trimestre" value="{{ $trimestre }}">
                <input type="hidden" name="disciplina" value="{{ $disciplina_id }}">
                <input type="hidden" name="ano" value="{{ $anoAtivo->ano }}">

            <div class="card-block row">
                <div class="col-sm-12 col-lg-12 col-xl-12">
                    <div class="table-responsive signal-table custom-scrollbar">
                        <table class="table table-hover" id="tabela-notas">
                            <thead>
                                <tr>
                                    <th scope="col" style="border: 0.2px solid black; width: 0.1%; "></th>
                                    <th scope="col"  style="border: 0.2px solid black; width: 0.6%; "></th>
                                    <th scope="col" style="border: 0.2px solid black; width: 0.08%;"></th>
                                    <th scope="col" colspan="9" style="border: 0.2px solid black; text-align: center; width: 1.5%;font-size: 12px;">
                                        {{ $classe->nome }} - {{ $nomeDisciplina->nome }} - {{ $trimestre }}º Trimestre - Ano: {{$anoAtivo->ano}}
                                    </th>
                                </tr>
                                <tr>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; width: 0.001%; ">Nº</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; width: 0.5%; ">Nome de Aluno</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black;">G</th>

                                    <!-- Trimestre ativo -->
                                    <th scope="col" colspan="4" style="border: 0.2px solid black;">ACS</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black;" hidden>MM</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black;">M.ACS</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black;">AT</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black;">MT</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black;">Comportamento</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black;">Sinalética</th>
                                </tr>
                                <tr>
                                    <!-- Testes -->
                                    @for ($i = 1; $i <= 4; $i++)
                                        <th scope="col" style="border: 0.2px solid black;">{{ $i }}ª</th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($alunosMatriculados->unique('aluno_id') as $matricula)
                                    @php
                                        $aluno = $matricula->aluno;
                                        $pauta = $pautas->firstWhere('aluno_id', $aluno->id);
                                        $numeroCampo = 'numero';
                                        $copCampo = 'cop' . $trimestre;
                                        $sinCampo = 'sin' . $trimestre;
                                    @endphp

                                    <tr>
                                        {{-- Número --}}
                                        <td style="border: 0.2px solid black; width: 0.5%; height: 0.01%; text-align: left; ">
                                            <input type="number" min="0" class="form-control input-pequeno"
                                                name="notas[{{ $aluno->id }}][{{ $numeroCampo }}]"
                                                value="{{ $pauta?->$numeroCampo ?? '' }}">
                                        </td>
                                        <td style="border: 0.2px solid black; width: 5%; height: 0.1%; text-align: left; ">{{ $aluno->pessoa->nome }}</td>
                                        <td>{{ substr($aluno->pessoa->genero, 0, 1) }}</td>

                                        {{-- Testes 1 a 4 --}}
                                        @php
                                            $testesValores = [];
                                        @endphp

                                        @for ($i = 1; $i <= 4; $i++)
                                            @php
                                                $campo = 'teste'.$i.'Tr'.$trimestre;
                                                $valor = $pauta?->$campo;
                                                if ($valor > 0) $testesValores[] = $valor;
                                            @endphp
                                            <td style="border: 0.2px solid black; width: 5%; height: 0.01%; text-align: left; ">
                                                <input type="number" step="0.01" min="0" max="20"
                                                    class="form-control input-pequeno"
                                                    name="notas[{{ $aluno->id }}][{{ $campo }}]"
                                                    value="{{ $valor > 0 ? $valor : '' }}">
                                            </td>
                                        @endfor

                                        {{-- Média dos testes (MM) --}}
                                        @php
                                            $mmCampo = 'mmTr'.$trimestre;
                                            $mmValor = $pauta?->$mmCampo;
                                            $mediaAcs = count($testesValores) > 0 ? round(array_sum($testesValores) / count($testesValores), 2) : 0;
                                        @endphp
                                        <td  hidden>
                                            <input type="number" step="0.01" min="0" max="20"
                                                class="form-control input-pequeno"
                                                name="notas[{{ $aluno->id }}][{{ $mmCampo }}]"
                                                value="{{ $mmValor > 0 ? $mmValor : '' }}">
                                        </td>

                                        {{-- M.ACS (média dos testes) - só leitura --}}
                                        <td style="text-align:center; font-size: 12px;">
                                            {{ $mediaAcs > 0 ? $mediaAcs : '' }}
                                        </td>

                                        {{-- Avaliação Trimestral (AT / AP) --}}
                                        @php
                                            $apCampo = 'apTr'.$trimestre;
                                            $apValor = $pauta?->$apCampo;
                                        @endphp
                                        <td>
                                            <input type="number" step="0.01" min="0" max="20"
                                                class="form-control input-pequeno"
                                                name="notas[{{ $aluno->id }}][{{ $apCampo }}]"
                                                value="{{ $apValor > 0 ? $apValor : '' }}">
                                        </td>

                                       {{-- MT (Média Trimestral) = (2*M.ACS + AT) / 3 --}}
                                        @php
                                            $mt = ($mediaAcs > 0 && $apValor > 0) ? round((2 * $mediaAcs + $apValor) / 3) : 0;
                                        @endphp
                                        <td style="text-align:center; font-size: 12px">
                                            {{ $mt > 0 ? $mt : '' }}
                                        </td>

                                        {{-- Comportamento --}}
                                        <td>
                                            <select class="form-control input-pequeno"
                                                name="notas[{{ $aluno->id }}][{{ $copCampo }}]">
                                                <option value="">Selecione</option>
                                                <option value="M. Bom" {{ $pauta?->$copCampo == 'M. Bom' ? 'selected' : '' }}>M. Bom</option>
                                                <option value="Bom" {{ $pauta?->$copCampo == 'Bom' ? 'selected' : '' }}>Bom</option>
                                                <option value="Medíocre" {{ $pauta?->$copCampo == 'Medíocre' ? 'selected' : '' }}>Medíocre</option>
                                                <option value="Mau" {{ $pauta?->$copCampo == 'Mau' ? 'selected' : '' }}>Mau</option>
                                            </select>
                                        </td>

                                        {{-- Sinalética --}}
                                        <td>
                                            <select class="form-control input-pequeno"
                                                name="notas[{{ $aluno->id }}][{{ $sinCampo }}]">
                                                <option value="">Selecione</option>
                                                <option value="PDF" {{ $pauta?->$sinCampo == 'PDF' ? 'selected' : '' }}>PDF</option>
                                                <option value="PPF" {{ $pauta?->$sinCampo == 'PPF' ? 'selected' : '' }}>PPF</option>
                                                <option value="PPC" {{ $pauta?->$sinCampo == 'PPC' ? 'selected' : '' }}>PPC</option>
                                                <option value="AM" {{ $pauta?->$sinCampo == 'AM' ? 'selected' : '' }}>AM</option>
                                                <option value="D" {{ $pauta?->$sinCampo == 'D' ? 'selected' : '' }}>D</option>
                                                <option value="T" {{ $pauta?->$sinCampo == 'T' ? 'selected' : '' }}>T</option>
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <thead>
                                
                                <tr>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; ">Nº</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black;">Nome de Aluno</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black;">G</th>

                                    <!-- Trimestre ativo -->
                                    <th scope="col" colspan="4" style="border: 0.2px solid black;">ACS</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black;" hidden>MM</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black;">M.ACS</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black;">AT</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black;">MT</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black;">Comportamento</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black;">Sinalética</th>
                                </tr>
                                <tr>
                                    <!-- Testes -->
                                    @for ($i = 1; $i <= 4; $i++)
                                        <th scope="col" style="border: 0.2px solid black;">{{ $i }}ª</th>
                                    @endfor
                                </tr>
                                <tr>
                                    <th scope="col" style="border: 0.2px solid black; "></th>
                                    <th scope="col" style="border: 0.2px solid black; "></th>
                                    <th scope="col" style="border: 0.2px solid black; width: 0.2%;"></th>
                                    <th scope="col" colspan="9" style="border: 0.2px solid black; text-align: center; width: 1.5%;">
                                        {{ $trimestre }}º Trimestre
                                    </th>
                                </tr>
                            </thead>
                        </table>
                        <br>
                         <center><button type="submit" class="btn btn-primary">Lancar Notas</button></center>
                        <br>
                    </div>
                </div>
            </div>
          </form>
        </div>
    </div>
</div>






@endsection

@push('js')

<script>
    function updateRoute() {
        const classe = document.getElementById('classe').value;
        const disciplina = document.getElementById('disciplina').value;
        const trimestre = document.getElementById('trimestre').value;
        const ano = {{$ano}};
        window.location.href = `/notas/${classe}/${disciplina}/${trimestre}/${ano}`;
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
        rules: {
            vaga: {
                required: true,
            }
        },
        submitHandler: function(form) {
            // Use FormData para suportar envio de arquivos
            var formData = new FormData(form);

            $.ajax({
                type: "POST",
                url: "{{ route('notas.update') }}",
                data: formData,
                processData: false, // Não processa os dados, necessário para FormData
                contentType: false, // Define o content type como multipart/form-data
                beforeSend: function() {
                    $('#botao_salvar').attr('disabled', true);
                    $('#icon_enviar').removeClass('ri-arrow-right-line').addClass('spinner-border ri-loader-2-line');
                    $('#botao_texto').text('Actualizar Utilizador...');
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
