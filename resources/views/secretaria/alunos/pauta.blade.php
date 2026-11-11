
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pauta de Aluno</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 8pt;
        }
        .header, .footer {
            text-align: center;
        }
        .content {
            margin: 20px 0;
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
            padding: 3px;
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
            text-align: right; /* Alinha o texto à direita */
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
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
  
    </style>
</head>
<body>

<div class="header">
    <img alt="{{$escola->logotipo}}" 
        src="{{ public_path('logotipo/'.$escola->logotipo) }}" 
        width="100" height="90"><br>
    <h4>{{ strtoupper($escola->nome) }}</h4>
    <h4>DIRECÇÃO DE REGISTO ACADÉMICO</h4>
    <h4><u>APROVEITAMENTO PEDAGÓGICO</u></h4>
</div>
<div class="content">

    @php
        use Illuminate\Support\Str;
    @endphp

    <p style="text-align: right;">
         Classe:<b> {{$turma->classe->nome}}, {{$turma->periodo->nome}} - {{$turma->nome}} </b>
    </p>

    <b>PAUTA ANUAL</b>  <i style="margin-left: 88%">Ano Lectivo: <b>{{$turma->anoAcademico->ano}}</b></i>
    <hr>

    <div class="card-block row">
        <div class="col-sm-12 col-lg-12 col-xl-12">
            <div class="table-responsive custom-scrollbar">
                <table class="table table-bordered" id="tabelas-notas">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 6%; border: 0.2px solid black; ">Codigo</th>
                            <th rowspan="2" style="width: 12%; border: 0.2px solid black; ">Nome</th>
                            <th rowspan="2" style="width: 2%;border: 0.2px solid black; background-color:rgb(255, 0, 140); color: white">G</th>
                            @foreach ($disciplinas as $disciplina)
                                <th colspan="4" style="border: 0.2px solid black; background-color:rgb(0, 195, 255); color: white">{{ $disciplina->nome }}</th>
                            @endforeach
                            <th rowspan="2" style="border: 0.2px solid black; background-color: #e6e600; color: white">MG</th>
                            <th rowspan="2" style="border: 0.2px solid black; background-color: #ffaa00; color: white">Resultado Final</th>
                        </tr>
                        <tr>
                            @foreach ($disciplinas as $disciplina)
                                <th style="border: 0.2px solid black;">1º T</th>
                                <th style="border: 0.2px solid black;">2º T</th>
                                <th style="border: 0.2px solid black;">3º T</th>
                                <th style="border: 0.2px solid black; background-color:rgb(255, 0, 140); color: white">M. Anual</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dadosTabela as $index => $aluno)
                            <tr>
                                <td style="border: 0.2px solid black;">{{ $aluno['codigo'] }}</td>
                                <td style="border: 0.2px solid black;">{{ $aluno['nome'] }}</td>
                                <td style="border: 0.2px solid black; background-color:rgb(255, 0, 140); color: white">{{ substr($aluno['genero'], 0, 1) }}</td>
                                @foreach ($aluno['disciplinas'] as $notas)
                                    <td style="border: 0.2px solid black;">{{ $notas['mediaTr1'] > 0 ? $notas['mediaTr1'] : ''}}</td>
                                    <td style="border: 0.2px solid black;">{{ $notas['mediaTr2'] > 0 ? $notas['mediaTr2'] : '' }}</td>
                                    <td >{{ $notas['mediaTr3'] > 0 ? $notas['mediaTr3'] : '' }}</td>
                                    <td style="border: 0.2px solid black; background-color:rgb(255, 0, 140); color: white">{{ $notas['mediaAnual'] > 0 ? $notas['mediaAnual'] : '' }}</td>
                                @endforeach
                                <td style="border: 0.2px solid black; background-color: #e6e600; color: white">{{ $aluno['mediaGeral'] > 0 ? $aluno['mediaGeral'] : '' }}</td>
                                <td style="border: 0.2px solid black; background-color: #ffaa00; color: white">{{ $aluno['mediaGeral'] > 0  ? $aluno['resultadoFinal'] : '' }}</td>
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
            O(a) Professor(a) <br>
            _________________________________________________
        </td>
        <td>
            O(a) DAE(a) <br>
            _________________________________________________
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
