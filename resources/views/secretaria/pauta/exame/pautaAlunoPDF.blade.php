
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pauta de Exame de Aluno</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 5px;
            font-size: 8pt;
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
    <b>Pauta de Aproveitamento Pedagógico - {{$matricula->anoAcademico->ano}}</b><br>
    <b>Exame da {{$matricula->classe->nome}}</b>
</div>
<div class="content">

    @php
        use Illuminate\Support\Str;
    @endphp

    <br><br>
    <b style="font-size:10pt ; ">Aluno: <u>{{$matricula->aluno->pessoa->nome}}</u></b>
    <div class="card-block row">
        <div class="col-sm-12 col-lg-12 col-xl-12">
            <div class="table-responsive custom-scrollbar">
                <table class="table table-bordered" id="tabelas-notas">
                <thead>
                    <tr>
                        <th  rowspan="2" style="border: 0.2px solid black;  width: 4%; text-align: center; ">Disciplina</th>
                        <th  rowspan="2" style="border: 0.2px solid black;  width: 3%; text-align: center;">Prov.</th>
                        <th scope="col" rowspan="2" style="border: 0.2px solid black; width: 3%; text-align: center;">NCD</th>
                        <th scope="col" colspan="2" style="border: 0.2px solid black; width: 3%; text-align: center;">Exame</th>
                        <th scope="col" rowspan="2" style="border: 0.2px solid black; width: 3%; text-align: center;">Voto</th>
                        <th scope="col" rowspan="2" style="border: 0.2px solid black; width: 3%; text-align: center;">NF</th>
                    </tr>
                    <tr>
                        <th scope="col"  style="border: 0.2px solid black; width: 3%; text-align: center;">1ª ch.</th>
                        <th scope="col"  style="border: 0.2px solid black; width: 3%; text-align: center;">2ª ch.</th>
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
                            <td style="border: 0.2px solid black; width: 0.1%; text-align: center;">{{ $pautasExame->proveniencia ?? '' }}</td>

                                <td style=" text-align: center;">{{ $pautasExame->ncd == 0 ? '' : $pautasExame->ncd }}</td>
                                <td style=" text-align: center;">{{ $pautasExame->primeira_chamada==0 ? '': $pautasExame->primeira_chamada }}</td>
                                <td style=" text-align: center;">{{ $pautasExame->segunda_chamada==0 ? '':  $pautasExame->segunda_chamada }}</td>
                                <td style=" text-align: center;">{{ $pautasExame->voto==0 ? '': $pautasExame->voto }}</td>
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
<div class="footer">
        <div>
             Impresso no dia {{ now()->format('d-M-Y H:i') }} por  <i><b>{{ Auth::user()->nome }}</b></i> site <u>http://lhaysso.edu.co.mz</u> <b>{{$escola->slogan}}</b>
                 
        </div>
 </div>

</body>
</html>
