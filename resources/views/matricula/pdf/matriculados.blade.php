
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
            font-size: 9pt;
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
    <h4><u>MAPA DE MATRICULA E RENOVAÇÃO</u></h4>
</div>
<div class="content">

    @php
        use Illuminate\Support\Str;
    @endphp


    <b>ALUNOS</b>  <i style="margin-left: 76%"> Ano Lectivo: <b>{{$anoAcademicoAtivo->ano}}</b></i>
    <hr> 
    <table class="table table-bordered" style="font-size: 7pt">
     @foreach($matriculados as $classe => $alunos)
        <tr>
            <th style="background-color:#808387; color: white;font-size: 10pt " colspan="10"><center>{{$classe}}</center></th>
        </tr>
        <tr>
            <th>#</th>
            <th>Codigo </th>
            <th>Nome Aluno</th>
            <th>Enc.</th>
            <th>referência</th>
            <th>Tipo</th>
            <th>Volor </th>
            <th>Estado</th>
            <th>Data Pag.</th>
            <th>Desc.</th>
        </tr>
        @php $orderNumber = 1; @endphp
        @foreach($alunos as $matricula)
            <tr>
                <td>{{ $orderNumber }}</td>
                <td>{{$matricula->aluno->codigo}}</td>
                <td>{{$matricula->aluno->pessoa->nome}}</td>
                <td>{{ optional($matricula->aluno->encarregado->first())->telefone }}</td>
                <td>{{$matricula->referencia}}</td>
                <td>
                    @if($matricula->renovacao==1)
                        <span style="color:#009DB5">Renovação</span>
                    @else
                        <span style="color:#F99B0D">N. Ingresso</span>
                    @endif
                </td>
                <td>{{ number_format($matricula->valor_total, 2, ',', '.') }}</td>
                <td>
                    @if($matricula->pagamento->pagou==1)
                        <span style="color:green">Pago ({{$matricula->pagamento->metodo->nome}})</span>
                    @else
                        <span style="color:red">Pendente</span>
                    @endif
                </td>
                <td>
                    @if($matricula->pagamento->pagou==1)
                        {{ \Carbon\Carbon::parse($matricula->pagamento->data_pagamento)->format('d-M-Y') }}
                    @else
                        <span>---</span>
                    @endif
                </td>
                <td>
                    @if($matricula->pagamento->pagou==1)
                        @if($matricula->pagamento->tipo==0)
                            <span>Com Isenção</span>
                        @elseif($matricula->pagamento->tipo==1)
                            <span>Com Disconto</span>
                        @elseif($matricula->pagamento->tipo==2)
                            <span>Normal</span>
                        @endif
                    @else
                        <span>---</span>
                    @endif
                </td>

                @php $orderNumber++;  @endphp
            </tr>
        @endforeach
    @endforeach
    </table>

    ER - Entidade e Referência<br>
    Cash - Validação Manual

    <br><br>
    <p style="text-align: right; font-size: 8pt;">
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
