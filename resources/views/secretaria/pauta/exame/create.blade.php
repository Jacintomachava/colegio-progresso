
@extends('layouts.app')

@push('css')

<style>

/* Estilo geral da tabela */
.table {
    border-collapse: collapse; /* Remove espaços entre células */
    width: 100%; /* Faz a tabela ocupar todo o espaço disponível */
    font-size: 12px; /* Define o tamanho padrão do texto */
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
    <div class="col-md-4"></div>
    <div class="col-md-6"></div>
    <div class="col-md-2" style="text-align: rigth;">
        <a href="{{route('pauta.exame',['classe'=>$classe->id,'ano' => $ano->ano])}}"  >
            <button class="btn btn-pill btn-secondary btn-sm">Pauta Final de Exame</button>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="card light-table-bg title-line">

          <form method="GET" action="{{ route('pautas.exames', [
                    'classe' => request('classe'),
                    'disciplina' => request('disciplina'),
                    'ano' => $ano->ano,
                ]) }}"  id="formNotas" >

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

                            

                        </div>
                    </h3>
                </div>
            </form>

          <form id="form" enctype="multipart/form-data">

            @csrf

            <div class="card-block row">
                <div class="col-sm-12 col-lg-12 col-xl-12">
                    <div class="table-responsive signal-table custom-scrollbar">
                        <table class="table table-hover" id="tabela-notas">
                            <thead>
                                <tr>
                                    <th scope="col" rowspan="3" style="border: 0.2px solid black; width: 0.1%; ">Nº</th>
                                    <th scope="col" rowspan="3" style="border: 0.2px solid black; width: 0.6%; text-align: left;">Nome</th>
                                    <th scope="col" rowspan="3" style="border: 0.2px solid black; width: 0.08%;">G</th>
                                    <th scope="col" rowspan="3" style="border: 0.2px solid black; width: 0.1%;">Prov.</th>
                                    <th scope="col" colspan="5" style="border: 0.2px solid black; text-align: center; width: 1.5%;font-size: 12px;">
                                        Exame de {{$classe->nome}} de {{$disciplina->nome}} Ano: {{$ano->ano}}
                                    </th>
                                </tr>
                                <tr>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; width: 0.08%;">NCD</th>
                                    <th scope="col" colspan="2" style="border: 0.2px solid black; width: 0.08%;">Exame</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; width: 0.08%;">Voto</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; width: 0.08%;">NF</th>
                                </tr>
                                <tr>
                                    <th scope="col"  style="border: 0.2px solid black; width: 0.08%;">1 ch.</th>
                                    <th scope="col"  style="border: 0.2px solid black; width: 0.08%;">2 ch.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pautas as $p)
                                <tr>
                                    <td >
                                        <input type="number" step="0.01" min="0" max="200" class="form-control"
                                            name="exame[{{ $p->id }}][numero]"
                                            value="{{ $p->numero }}" style="text-align: center;">
                                    </td>
                                    <td style="text-align: left;">{{ $p->aluno->pessoa->nome }}</td>
                                    <td>{{ substr($p->aluno->pessoa->genero, 0, 1) }}</td>

                                    {{-- proveniência --}}
                                    <td >
                                        <input type="text" class="form-control"
                                            name="exame[{{ $p->id }}][proveniencia]"
                                            value="{{ $p->proveniencia }}">
                                    </td>

                                    {{-- NCD --}}
                                    <td style="text-align: center;">
                                        <input type="number" step="0.01" min="0" max="20" class="form-control"
                                            name="exame[{{ $p->id }}][ncd]"
                                            value="{{ $p->ncd }}" style="text-align: center;">
                                    </td>

                                    {{-- 1ª Chamada --}}
                                    <td >
                                        <input type="number" step="0.01" min="0" max="20" class="form-control"
                                            name="exame[{{ $p->id }}][primeira]"
                                            value="{{ $p->primeira_chamada }}" style="text-align: center;">
                                    </td>

                                    {{-- 2ª Chamada --}}
                                    <td >
                                        <input type="number" step="0.01" min="0" max="20" class="form-control"
                                            name="exame[{{ $p->id }}][segunda]"
                                            value="{{ $p->segunda_chamada }}" style="text-align: center;">
                                    </td>

                                    {{-- voto --}}
                                    <td >
                                        <input type="number" step="0.01" min="0" max="2" class="form-control"
                                            name="exame[{{ $p->id }}][voto]"
                                            value="{{ $p->voto }}" style="text-align: center;">
                                    </td>

                                    {{-- NF calculada --}}
                                    <td style="text-align: center;">
                                        <strong>
                                            {{ $p->nf ?? '' }}
                                        </strong>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
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
        window.location.href = `/pauta/exame/${classe}/${disciplina}`;
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
                url: "{{ route('salvar.exame') }}",
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
