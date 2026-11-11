
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha de Matricula</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 10px;
            font-size: 7pt;
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
            border: 1px solid #dee2e6;
        }
        .table-bordered td, .table-bordered th {
            border: 1px solid #dee2e6;
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
        width="70" height="70"><br>
    <b>{{ $escola->nome }}</b><br>
    <b>Pauta de Aproveitamento Pedagógico - {{$anoAtivo->ano}}</b><br>
    <b>{{$matricula->classe->nome}} - {{$matricula->anoAcademico->ano}}</b>

</div>
<div class="content">

    @php
        use Illuminate\Support\Str;
    @endphp


        <b style="font-size:10pt">Aluno: {{$aluno->pessoa->nome}}</b>
        <div class="col-sm-12">
        <div class="light-table-bg title-line">

            @php
                $totalDisciplinas = 0;  
                $totalNotasFinaisDisciplina = 0;
                $pautaFinal = 0;
            @endphp
            <div class="card-block row">
                <div class="col-sm-12 col-lg-12 col-xl-12">
                    <div class="table-responsive signal-table custom-scrollbar">
                        <table class="table table-hover" id="tabela-notas">
                            <thead>
                                <tr>
                                    <th scope="col" colspan="1" style="border: 0.2px solid black; width: 12%;"></th>
                                    <th scope="col" style="border: 0.2px solid black; width: 2%; text-align: center;"></th>
                                    <th scope="col" colspan="9" style="border: 0.2px solid black; text-align: center; width: 10%; ">1º Trimestre</th>
                                    <th scope="col" colspan="9" style="border: 0.2px solid black; text-align: center;  width: 10%; ">2º Trimestre</th>
                                    <th scope="col" colspan="9" style="border: 0.2px solid black; text-align: center;  width: 10%; ">3º Trimestre</th>
                                    <th scope="col" rowspan="3" style="border: 0.2px solid black; text-align: center; width: 2%;  ">M. Anual</th>
                                </tr>
                                <tr>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black;  ">Disciplinas</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; text-align: center;">T</th>

                                    <!-- 1º Trimestre -->
                                    <th scope="col" colspan="4" style="border: 0.2px solid black; text-align: center;">ACS</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; text-align: center;">M.<br>ACS</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; text-align: center;">AP</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; text-align: center;">MT</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; ">Cop.</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; ">Sin.</th>

                                    <!-- 2º Trimestre -->
                                    <th scope="col" colspan="4" style="border: 0.2px solid black; text-align: center;">ACS</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; text-align: center;">M.<br>ACS</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; text-align: center;">AP</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; text-align: center;">MT</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; ">Cop.</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; ">Sin.</th>

                                    <!-- 3º Trimestre -->
                                    <th scope="col" colspan="4" style="border: 0.2px solid black; text-align: center;">ACS</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; text-align: center;">M.<br>ACS</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; text-align: center;">AP</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; text-align: center;">MT</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; ">Cop.</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; ">Sin.</th>
                                </tr>
                                <tr>

                                    <!-- Testes -->
                                    @for ($i = 1; $i <= 4; $i++)
                                        <th scope="col" style="border: 0.2px solid black; text-align: center;">{{ $i }}ª</th>
                                    @endfor

                                    @for ($i = 1; $i <= 4; $i++)
                                        <th scope="col" style="border: 0.2px solid black; text-align: center;">{{ $i }}ª</th>
                                    @endfor

                                    @for ($i = 1; $i <= 4; $i++)
                                        <th scope="col" style="border: 0.2px solid black; text-align: center;">{{ $i }}ª</th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pautas as $aluno)
                                    <tr>
                                        <td style="border: 0.2px solid black; text-align: left;">{{ $aluno->disciplina->nome }}</td>
                                        <td style="border: 0.2px solid black; text-align: center;">
                                          @if($aluno->disciplina->co_curricular==1)
                                            CC
                                          @else  
                                            C
                                          @endif
                                        </td>

                                        @php
                                        
                                           $mediaFinal = 0; 
                                           $pautaFinal = $anoAtivo->pauta_finalizada;

                                        @endphp

                                        <!-- Trimestres -->
                                        @foreach(range(1, 3) as $trimestre)
                                            @php
                                                // Coleta os testes do trimestre
                                                $testes = [
                                                    $aluno->{'teste1Tr' . $trimestre},
                                                    $aluno->{'teste2Tr' . $trimestre},
                                                    $aluno->{'teste3Tr' . $trimestre},
                                                    $aluno->{'teste4Tr' . $trimestre},
                                                    //$aluno->{'mmTr' . $trimestre},
                                                ];

                                                // Filtra os testes válidos
                                                $testesValidos = array_filter($testes, fn($t) => $t > 0);

                                                // Média trimestral (evitando divisão por zero)
                                                $mediaTestes = count($testesValidos) > 0
                                                    ? array_sum($testesValidos) / count($testesValidos)
                                                    : 0;

                                                // Nota final do trimestre (MT)
                                                $notaFinalTrimestre = (2*$mediaTestes+$aluno->{'apTr' . $trimestre})/3 ?? 0;
                                                $mediaFinal = $mediaFinal + $notaFinalTrimestre;
                                            @endphp

                                            <!-- Exibição das notas -->
                                            @foreach($testes as $teste)
                                                <td style="border: 0.2px solid black; text-align: center; " @if($loop->iteration==5)   @else   @endif >{{ $teste > 0 ? $teste : '' }} </td>
                                            @endforeach

                                            <!-- Cálculos -->
                                            <td style="border: 0.2px solid black; text-align: center; ">{{ $mediaTestes > 0 ? number_format($mediaTestes, 2) : '' }}</td>
                                            <td style="border: 0.2px solid black; text-align: center;">{{ $aluno->{'apTr' . $trimestre} > 0 ? $aluno->{'apTr' . $trimestre} : '' }}</td>
                                            <td style="border: 0.2px solid black; text-align: center;">@if($aluno->{'apTr' . $trimestre}>0) {{ $notaFinalTrimestre > 0 ? round($notaFinalTrimestre) : '' }} @else  @endif </td>
                                            <td style="border: 0.2px solid black; ">@if($aluno->{'cop'. $trimestre}!=0 && $aluno->{'cop'. $trimestre}!=null ) {{ $aluno->{'cop'. $trimestre} }} @else @endif </td>
                                            <td style="border: 0.2px solid black; ">@if($aluno->{'sin'. $trimestre}!=0 && $aluno->{'sin'. $trimestre}!=null ) {{ $aluno->{'sin'. $trimestre} }} @else @endif </td>
                                        @endforeach


                                        <td style="border: 0.2px solid black; text-align: center; ">
                                          @if($aluno->apTr1 > 0 && $aluno->apTr2 > 0 && $aluno->apTr3 > 0) 
                                            {{ round($mediaFinal > 0 ? $mediaFinal/3 : '') }}
                                            @else  

                                          @endif
                                          @php
                                              if($aluno->apTr3 > 0 && $aluno->disciplina->co_curricular == 0) {
                                                  $totalDisciplinas++;  
                                                  $totalNotasFinaisDisciplina += round($mediaFinal / 3);
                                              }
                                          @endphp
                                        </td>
                                    </tr>
                                @endforeach
                                    <tr>
                                        <th scope="col" colspan="25" style="border: 0.2px solid black; width: 4.5%; text-align: center;">Aproveitamento Pedagogico</th>
                                        <th scope="col" colspan="4" style="border: 0.2px solid black; text-align: center; width: 10%; text-align: center;">
                                          @php
                                              $aproveitamento = '';
                                              if ($totalDisciplinas > 0 && $pautaFinal==1) {
                                                  if($matricula->classe->tem_exame==0){
                                                    $aproveitamento = ($totalNotasFinaisDisciplina / $totalDisciplinas) > 9.5 ? 'Progride' : 'Não Progride';
                                                  }else{
                                                    $aproveitamento = ($totalNotasFinaisDisciplina / $totalDisciplinas) > 9.5 ? 'Admitido' : 'Não Admitido';
                                                  }
                                              }
                                          @endphp
                                          {{ $aproveitamento }}
                                        </th>
                                        <th scope="col" rowspan="1" style="border: 0.2px solid black; text-align: center; width: 2%;text-align: center; ">
                                            @if($totalDisciplinas > 0 && $pautaFinal==1) 
                                                {{ round($totalNotasFinaisDisciplina / $totalDisciplinas) }} 
                                            @endif
                                            
                                        </th>
                                    </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
       </div>


</div>



   

</div>
<div class="footer">
        <div>
             Impresso no dia {{ now()->format('d-M-Y H:i') }} por utilizador: <i><b>{{ Auth::user()->nome }}</b></i> site <u>http://lhaysso.edu.co.mz</u> <b>{{$escola->slogan}}</b>
                 
        </div>
 </div>

</body>
</html>
