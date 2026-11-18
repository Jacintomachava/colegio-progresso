<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">

    <title>Preçário Escolar</title>

    <style>
        @page {
            margin: 35px 35px 45px 35px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #263238;
        }

        .cabecalho {
            text-align: center;
            border-bottom: 2px solid #17426b;
            padding-bottom: 14px;
            margin-bottom: 20px;
        }

        .nome-escola {
            font-size: 19px;
            font-weight: bold;
            color: #17426b;
            margin-bottom: 5px;
        }

        .titulo {
            font-size: 15px;
            font-weight: bold;
            margin-top: 12px;
        }

        .subtitulo {
            color: #64748b;
            margin-top: 5px;
        }

        .secao {
            background: #17426b;
            color: white;
            padding: 9px 10px;
            font-size: 11px;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        th {
            background: #e8eef5;
            color: #17426b;
            font-weight: bold;
            text-align: left;
            padding: 9px 7px;
            border: 1px solid #cbd5e1;
        }

        td {
            padding: 8px 7px;
            border: 1px solid #cbd5e1;
        }

        tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .valor {
            text-align: right;
            white-space: nowrap;
        }

        .nota {
            margin-top: 18px;
            padding: 12px;
            background: #f1f5f9;
            border-left: 3px solid #17426b;
            line-height: 1.6;
        }

        .rodape {
            position: fixed;
            bottom: -25px;
            left: 0;
            right: 0;
            border-top: 1px solid #ddd;
            padding-top: 8px;
            font-size: 8px;
            color: #64748b;
            text-align: center;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }
    </style>
</head>

<body>

    {{-- CABEÇALHO --}}
    <div class="cabecalho">

        <div class="nome-escola">
            {{ $escola->nome }}
        </div>

        <div class="titulo">
            PREÇÁRIO ESCOLAR
        </div>

        <div class="subtitulo">
            Ano Académico {{ $ano->ano }}
        </div>

    </div>

    {{-- VALORES POR CLASSE --}}
    <div class="secao">
        1. MATRÍCULAS, RENOVAÇÕES E PROPINAS
    </div>

    <table>
        <thead>
            <tr>
                <th>Classe</th>
                <th class="valor">Matrícula</th>
                <th class="valor">Renovação</th>
                <th class="valor">Propina mensal</th>
            </tr>
        </thead>

        <tbody>
            @forelse($valores as $valor)
                <tr>
                    <td>{{ $valor->classe_nome }}</td>

                    <td class="valor">
                        {{ number_format($valor->valor_matricula, 2, ',', '.') }} MT
                    </td>

                    <td class="valor">
                        {{ number_format($valor->valor_renovacao, 2, ',', '.') }} MT
                    </td>

                    <td class="valor">
                        {{ number_format($valor->valor_propina, 2, ',', '.') }} MT
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align:center;">
                        Nenhum valor configurado.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ACTIVIDADES --}}
    <div class="secao">
        2. SERVIÇOS E ACTIVIDADES EXTRACURRICULARES
    </div>

    <table>
        <thead>
            <tr>
                <th>Actividade</th>
                <th>Categoria</th>
                <th>Periodicidade</th>
                <th class="valor">Valor</th>
            </tr>
        </thead>

        <tbody>
            @forelse($actividades as $actividade)
                <tr>
                    <td>{{ $actividade->nome }}</td>

                    <td>
                        @switch($actividade->tipo)
                            @case('SERVICO')
                                Serviço
                                @break

                            @case('AULA_EXTRA')
                                Aula extra
                                @break

                            @case('CURSO_FERIAS')
                                Curso de férias
                                @break
                        @endswitch
                    </td>

                    <td>
                        {{ $actividade->periodicidade === 'MENSAL'
                            ? 'Mensal'
                            : 'Pagamento único' }}
                    </td>

                    <td class="valor">
                        {{ number_format($actividade->valor_mensal, 2, ',', '.') }} MT
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align:center;">
                        Nenhuma actividade disponível.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="nota">
        <strong>Informações:</strong>
        <div>
            Os valores apresentados correspondem ao preçário
            configurado no sistema na data de emissão.
        </div>
        <div>
            A periodicidade indicada determina se a actividade
            tem cobrança mensal ou única.
        </div>
    </div>

    <div class="rodape">
        {{ $escola->nome }}
        — Preçário {{ $ano->ano }}
        — Emitido em {{ now()->format('d/m/Y H:i') }}
    </div>

</body>
</html>