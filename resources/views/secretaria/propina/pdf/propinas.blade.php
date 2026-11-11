
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha de Propina</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 7pt;
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
            border: 1px solid #dee2e6;
        }
        .table-bordered td, .table-bordered th {
            border: 1px solid #dee2e6;
        }
        .right-align1 {
            float: right;
        }
        .watermark {
            position: fixed;
            top: 70%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            z-index: 0;
            text-align: center;
            opacity: 0.9;
            font-size: 40px;
            color: green;
            pointer-events: none;
        }
        /* Footer */
        .footer {
            position: fixed;
            bottom: 20px;
            left: 0;
            right: 0;
            width: 100%;
            text-align: center;
            font-size: 9px;
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
    <h4><u>MAPAS DE PROPINAS</u></h4>
    <h4><center><b>MÊS: {{strtoupper($mesSelecionado->nome)}} - {{$anoAcademicoAtivo->ano}}</b></center></h4>
</div>
<div class="content">

    @php
        use Illuminate\Support\Str;
    @endphp

    @php
        $NumeroAlunos = 0;
        $totalPagos = 0;
        $totalPendentes = 0;
        $totalEsperado = 0;

        $multas = 0;
        $isencoes = 0;
        $desconto = 0;
        $cocurricular = 0 ;
    @endphp

    @foreach($mensalidadesPorMes as $classeNome => $mensalidades)

        @foreach($mensalidades as $mensalidade)
            
            @php

                $NumeroAlunos++;

                $valorTotal = ($mensalidade->valor + $mensalidade->valor_co_curricular)
                            - $mensalidade->desconto
                            + ($mensalidade->multa/100 * ($mensalidade->valor + $mensalidade->valor_co_curricular));

                if($mensalidade->pagamento->tipo!=0){

                    $totalEsperado += $valorTotal;
                }

                if ($mensalidade->pagamento->pagou == 1) {
                    
                    if($mensalidade->pagamento->tipo!=0){
                        $totalPagos += $valorTotal;
                    }

                    if($mensalidade->pagamento->tipo==0){
                        $isencoes += $valorTotal;
                    }

                    if($mensalidade->pagamento->tipo==2){
                        $desconto += $mensalidade->desconto;
                    }

                    $cocurricular += $mensalidade->valor_co_curricular; 

                    $multas += ($mensalidade->multa/100 * ($mensalidade->valor + $mensalidade->valor_co_curricular));

                } else {
                    $totalPendentes += $valorTotal;
                }

            @endphp

        @endforeach

            @php

            @endphp

    @endforeach


    <hr>
    <b>PROPINAS DOS ALUNOS</b>  
    <i style="margin-left: 70%"> Ano Lectivo: <b>{{$anoAcademicoAtivo->ano}}</b></i><br>
    
    <hr>


    <table width="100%" cellspacing="10">
        <tr>
            <td style="background-color: rgba(26, 101, 161, 0.7); color: white; text-align: center; width: 25%; border-radius: 5px; padding: 15px; font-size: 11pt;">
                <p><b>Total Alunos</b></p>
                <h4>{{ number_format($NumeroAlunos, 0, ',', '.') }}</h4>
            </td>
            <td style="background-color: rgba(176, 179, 24, 0.88); color: white; text-align: center; width: 25%; border-radius: 5px; padding: 15px; font-size: 11pt;">
                <p><b>Total Esperado</b></p>
                <h4>{{ number_format($totalEsperado, 2, ',', '.') }}</h4>
            </td>

            <td style="background-color: #d1530bff; color: white; text-align: center; width: 25%; border-radius: 5px; padding: 15px; font-size: 11pt;">
                <p><b>Total Pendente</b></p>
                <h4>{{ number_format($totalPendentes, 2, ',', '.') }}</h4>
            </td>
            <td style="background-color: rgba(14, 168, 61, 0.88); color: white; text-align: center; width: 25%; border-radius: 5px; padding: 15px; font-size: 11pt; ">
                <p><b>Total Pago</b></p>
                <h4>{{ number_format($totalPagos, 2, ',', '.') }}</h4>
            </td>
        </tr>
    </table>

    <table width="100%" cellspacing="10">
        <tr>
            <td style="background-color:  rgba(14, 168, 61, 0.88); color: white; text-align: center; width: 25%; border-radius: 5px; padding: 15px; font-size: 11pt;">
                <p><b>Total Multa</b></p>
                <h4>{{ number_format($multas, 2, ',', '.') }}</h4>
            </td>
            <td style="background-color: #d1530bff; color: white; text-align: center; width: 25%; border-radius: 5px; padding: 15px; font-size: 11pt;">
                <p><b>Total Isenções</b></p>
                <h4>{{ number_format($isencoes, 2, ',', '.') }}</h4>
            </td>

            <td style="background-color:  rgba(176, 179, 24, 0.88); color: white; text-align: center; width: 25%; border-radius: 5px; padding: 15px; font-size: 11pt;">
                <p><b>Total Desconto</b></p>
                <h4>{{ number_format($desconto, 0, ',', '.') }}</h4>
            </td>
            <td style="background-color: rgba(26, 101, 161, 0.7); color: white; text-align: center; width: 25%; border-radius: 5px; padding: 15px; font-size: 11pt; ">
                <p><b>Total Co-Curricular</b></p>
                <h4>{{ number_format($cocurricular, 2, ',', '.') }}</h4>
            </td>
        </tr>
    </table>


    <br><br>

   @foreach($mensalidadesPorMes as $classeNome => $mensalidades)
    
        <table class="table table-bordered" style="font-size: 7pt; width: 100%">
            <thead>
                <tr style="font-weight: bold; background: #f2f2f2; color: #e61313ff ; font-size: 8pt;">
                    <td colspan="10" align="center"><h4>{{strtoupper($classeNome) }}</h4></td>
                </tr>    
                <tr>
                    <th>Codigo</th>
                    <th>Nome Aluno</th>
                    <th>Curricular</th>
                    <th>Co-Curr.</th>
                    <th>Multa</th>
                    <th>Desc.</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Data Pag.</th>
                    <th>Descrição</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalClasse = 0;
                    $totalPagos = 0;
                    $totalPendentes = 0;
                @endphp

                @foreach($mensalidades as $mensalidade)
                    @php
                        $valorTotal = ($mensalidade->valor + $mensalidade->valor_co_curricular)
                            - $mensalidade->desconto
                            + ($mensalidade->multa/100 * ($mensalidade->valor + $mensalidade->valor_co_curricular));

                        
                        if($mensalidade->pagamento->tipo!=0){

                            $totalClasse += $valorTotal;
                        }

                        if ($mensalidade->pagamento->pagou == 1) {
                            
                             if($mensalidade->pagamento->tipo!=0){
                                $totalPagos += $valorTotal;
                             }

                        } else {
                            $totalPendentes += $valorTotal;
                        }
                    @endphp

                    <tr>
                        <td>{{$mensalidade->aluno->codigo}}</td>
                        <td>{{$mensalidade->aluno->pessoa->nome}}</td>
                        <td>{{ number_format($mensalidade->valor, 2, ',', '.') }}</td>
                        <td>{{ number_format($mensalidade->valor_co_curricular, 2, ',', '.') }}</td>
                        <td>{{$mensalidade->multa}}%</td>
                        <td>{{$mensalidade->desconto}}</td>
                        <td>
                            
                            @if($mensalidade->pagamento->tipo==0)
                                {{ number_format(0, 2, ',', '.') }}
                            @else
                                {{ number_format($valorTotal, 2, ',', '.') }}
                            @endif
                        </td>
                        <td>
                            @if($mensalidade->pagamento->pagou==1)
                                <span style="color:green">Pago ({{$mensalidade->pagamento->metodo->nome}})</span>
                            @else
                                <span style="color:red">Pendente</span>
                            @endif
                        </td>
                        <td>
                            @if($mensalidade->pagamento->pagou==1)
                                {{ \Carbon\Carbon::parse($mensalidade->pagamento->data_pagamento)->format('d-M-Y') }}
                            @else
                                <span>---</span>
                            @endif
                        </td>
                        <td>
                            @if($mensalidade->pagamento->pagou==1)
                                @if($mensalidade->pagamento->tipo==0)
                                    <span>Isenção</span>
                                @elseif($mensalidade->pagamento->tipo==1)
                                    <span>Com Desconto</span>
                                @elseif($mensalidade->pagamento->tipo==2)
                                    <span>Normal</span>
                                @endif
                            @else
                                <span>---</span>
                            @endif
                        </td>
                    </tr>
                @endforeach

                <!-- Linha de totais no fim da tabela -->
                <tr style="font-weight: bold; background: #f2f2f2; font-size: 7pt;">
                    <td colspan="6" align="right">Totais da Classe:</td>
                    <td>{{ number_format($totalClasse, 2, ',', '.') }}</td>
                    <td colspan="3">
                        Pago: {{ number_format($totalPagos, 2, ',', '.') }} ---
                        Pendente: {{ number_format($totalPendentes, 2, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>
        <br>
    @endforeach



    <br><br>
    <p style="text-align: justify; font-size: 8pt;">
        <i>Impresso no dia {{ now()->format('d-M-Y H:i') }}</i>
    </p>

    <br>
    <table style="width: 100%; text-align: center; margin-top: 20px;">
        <tr>
            <td>
                O(a) Secretaria(o) <br>
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
            Contactos  <i><b>{{$escola->telefone}}/{{$escola->telefone2}}   </b></i> site <u>https://lhaysso.ac.mz</u> WhatsApp +258 86 824 4468 <b>{{$escola->slogan}}</b>   
        </div>
 </div>

</body>
</html>
