
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pauta da {{$minhaClasse->nome}} Ano {{$anoAcademico->ano}}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 5px;
            font-size: 6pt;
        }
        .header, .footer {
            text-align: center;
        }
        .content {
            margin: 10px 0;
        }
        .section-title {
            font-weight: bold;
            margin-top: 20px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;

        }
        .table, .table th, .table td {
            border: 1px solid #000;
        }
        .table th, .table td {
            padding: 1.5px;
            vertical-align: top;
            border-top: 1px solid #dee2e6;
        }
        .note {
            margin-top: 20px;
            font-size: 0.9em;
            color: #555;
        }
        .flex-container {
            display: flex;
            justify-content: space-between; /* Alinha os elementos ao máximo nas extremidades */
            align-items: center; /* Centraliza verticalmente os elementos */
        }
        .right-align {
            text-align: center; /* Alinha o texto à direita */
        }
        .container {
            width: 100%;
            padding-right: 15px;
            padding-left: 15px;
            margin-right: auto;
            margin-left: auto;
        }
        .row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px;
        }
        .col-md-4 {
            position: relative;
            width: 100%;
            padding-right: 15px;
            padding-left: 15px;
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
        }
        .table-bordered {
            border: 0.2px solid black;
        }
        .table-bordered td, .table-bordered th {
            border: 0.2px solid black;
        }
        .right-align1 {
            float: right;
        }
        /* Footer */
        .footer {
            position: fixed;
            bottom: 20px;
            left: 0;
            right: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
  
    </style>
</head>
<body>

<div class="header" style="font-size:10pt ; ">
    <img alt="{{$escola->logotipo}}" 
        src="{{ public_path('logotipo/'.$escola->logotipo) }}" 
        width="100" height="90"><br>
    <b>{{ $escola->nome }}</b><br>
    <b>Pauta de Aproveitamento Pedagógico - {{$anoAcademico->ano}}</b><br>
    <b>{{$minhaClasse->nome}}</b>
</div>
<div class="content">

    @php
        use Illuminate\Support\Str;
    @endphp


    <b style="font-size:10pt ; ">Director(a) da Turma:______________________________________________</b>
    <div class="card-block row">
        <div class="col-sm-12 col-lg-12 col-xl-12">
            <div class="table-responsive custom-scrollbar">
                <table class="table table-bordered" id="tabelas-notas" style="text-align: center;">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 1.2%; border: 0.2px solid black; ">N°</th>
                            <th rowspan="2" style="width: 9.7%; border: 0.2px solid black; ">Nome</th>
                            <th rowspan="2" style="width: 1%;border: 0.2px solid black; ">G</th>
                            @foreach ($disciplinas as $disciplina)
                                <th colspan="4" style="border: 0.2px solid black; ">{{ $disciplina['nome'] }}</th>
                            @endforeach
                            
                            {{-- Novos campos antes de MG --}}
                            <th colspan="3" style="border: 0.2px solid black;">Comportamento</th>
                            <th colspan="3" style="border: 0.2px solid black;">Sinalética</th>

                            <th rowspan="2" style="border: 0.2px solid black; width: 1%; ">MG</th>
                            <th rowspan="2" style="border: 0.2px solid black; ">R. Final</th>
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
                                <td style="border: 0.2px solid black; text-align: left; ">{{ $aluno['nome'] }}</td>
                                <td style="border: 0.2px solid black; ">{{ substr($aluno['genero'], 0, 1) }}</td>
                                @foreach ($aluno['disciplinas'] as $notas)
                                    <td style="border: 0.2px solid black;">{{ $notas['mediaTr1'] > 0 ? $notas['mediaTr1'] : ''}}</td>
                                    <td style="border: 0.2px solid black;">{{ $notas['mediaTr2'] > 0 ? $notas['mediaTr2'] : '' }}</td>
                                    <td >{{ $notas['mediaTr3'] > 0 ? $notas['mediaTr3'] : '' }}</td>
                                    <td style="border: 0.2px solid black; ">{{ $notas['mediaAnual'] > 0 ? $notas['mediaAnual'] : '' }}</td>
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
                                <td style="border: 0.2px solid black; ">{{ $aluno['mediaGeral'] > 0 ? $aluno['mediaGeral'] : '' }}</td>
                                <td style="border: 0.2px solid black; ">{{ $aluno['mediaGeral'] > 0  ? $aluno['resultadoFinal'] : '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<br>
<table style="width: 100%; text-align: center; margin-top: 20px;">
    <tr>
        <td>
            <b>1º Trimestre</b><br>
            O(a) Professor(a) <br>
            _________________________________<br>
            _____/_____/ {{$anoAcademico->ano}}
        </td>
        <td>
            <b>1º Trimestre</b><br>
            O(a) Professor(a) <br>
            _________________________________<br>
            _____/_____/ {{$anoAcademico->ano}}
        </td>
        <td>
            <b>1º Trimestre</b><br>
            O(a) Professor(a) <br>
            _________________________________<br>
            _____/_____/ {{$anoAcademico->ano}}
        </td>
        <td>
            <b>1º Trimestre</b><br>
            O(a) Professor(a) <br>
            _________________________________<br>
            _____/_____/ {{$anoAcademico->ano}}
        </td>
        <td>
            <b>1º Trimestre</b><br>
            O(a) Director Adjunto Pedagógico <br>
            _________________________________<br>
            _____/_____/ {{$anoAcademico->ano}}
        </td>
    </tr>
    <br><br>
    <tr>
        <td>
            <b>2º Trimestre</b><br>
            O(a) Professor(a) <br>
            _________________________________<br>
            _____/_____/ {{$anoAcademico->ano}}
        </td>
        <td>
            <b>2º Trimestre</b><br>
            O(a) Professor(a) <br>
            _________________________________<br>
            _____/_____/ {{$anoAcademico->ano}}
        </td>
        <td>
            <b>2º Trimestre</b><br>
            O(a) Professor(a) <br>
            _________________________________<br>
            _____/_____/ {{$anoAcademico->ano}}
        </td>
        <td>
            <b>2º Trimestre</b><br>
            O(a) Professor(a) <br>
            _________________________________<br>
            _____/_____/ {{$anoAcademico->ano}}
        </td>
        <td>
            <b>2º Trimestre</b><br>
            O(a) Director Adjunto Pedagógico <br>
            _________________________________<br>
            _____/_____/ {{$anoAcademico->ano}}
        </td>
    </tr>
    <br><br>
    <tr>
        <td>
            <b>3º Trimestre</b><br>
            O(a) Professor(a) <br>
            _________________________________<br>
            _____/_____/ {{$anoAcademico->ano}}
        </td>
        <td>
            <b>3º Trimestre</b><br>
            O(a) Professor(a) <br>
            _________________________________<br>
            _____/_____/ {{$anoAcademico->ano}}
        </td>
        <td>
            <b>3º Trimestre</b><br>
            O(a) Professor(a) <br>
            _________________________________<br>
            _____/_____/ {{$anoAcademico->ano}}
        </td>
        <td>
            <b>3º Trimestre</b><br>
            O(a) Professor(a) <br>
            _________________________________<br>
            _____/_____/ {{$anoAcademico->ano}}
        </td>
        <td>
            <b>3º Trimestre</b><br>
            O(a) Director Adjunto Pedagógico <br>
            _________________________________<br>
            _____/_____/ {{$anoAcademico->ano}}
        </td>
    </tr>
</table>



</div>
<div class="footer">
        <div>
             Impresso no dia {{ now()->format('d-M-Y H:i') }} por  <i><b>{{ Auth::user()->nome }}</b></i> site <u>http://lhaysso.edu.co.mz</u> <b>{{$escola->slogan}}</b>
                 
        </div>
 </div>

</body>
</html>
