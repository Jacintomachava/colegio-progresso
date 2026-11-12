
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
            margin: 20px;
            font-size: 10pt;
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
    <img alt="{{$matricula->escola->logotipo}}" 
        src="{{ public_path('logotipo/'.$matricula->escola->logotipo) }}" 
        width="100" height="90"><br>
    <h4>{{ strtoupper($matricula->escola->nome) }}</h4>
    <h4>DIRECÇÃO DE REGISTO ACADÉMICO</h4>
    <h4><u>FICHA DA MATRICULA</u></h4>
</div>
<div class="content">

    @php
        use Illuminate\Support\Str;
    @endphp

    <p style="text-align: right;">
         Factura nº:<b> #{{$matricula->pagamento->referencia}}</b>
    </p>

    <b>DADOS ESTUDANTE</b>  <i style="margin-left: 63%">Ano Lectivo: <b>{{$matricula->anoAcademico->ano}}</b></i>
    <hr>
     Estudante: <b>{{ Str::upper($matricula->aluno->pessoa->nome) }} </b> <span style="margin-left: 10%">Nº:</span> <b>{{$matricula->aluno->codigo}}</b>
     <br>
     Classe: <b>{{$matricula->classe->nome}}</b>  <span style="margin-left: 10%">Regime: </span><b>@if($matricula->periodo_id!=null) {{$matricula->periodo->detalhe}} @endif  </b>
     <span style="margin-left: 10%">{{$matricula->aluno->pessoa->tipo_documento}}:  </span><b>{{$matricula->aluno->pessoa->numero_documento}}</b><br>
     Local: <b>{{Str::upper($matricula->escola->endereco)}}</b></p>
    <hr>

    
    <table class="table table-bordered" style="font-size: 9pt">
        <tr>
            <th colspan="5" style="background-color:#808387; color: white "><center>DADOS DA MATRICULA</center></th>
        </tr>
        <tr>
            <td><b>ANO LECTIVO</b></td>
            <td><b>MATRICULA</b></td>
            <td><b>CLASSE</b></td>
            <td><b>REGIME</b></td>
            <td><b>DATA MATRICULA</b></td>
        </tr>
        <tr>
            <td>{{$matricula->anoAcademico->ano}}</td>
            <td>{{$matricula->codigo}}</td>
            <td>{{e($matricula->classe->nome)}}</td>
            <td>@if($matricula->periodo_id!=null) {{$matricula->periodo->detalhe}} @endif </td>
            <td>{{ \Carbon\Carbon::parse($matricula->created_at)->format('d-M-Y') }}</td>
        </tr>
    </table>

    <table class="table table-bordered" style="font-size: 9pt">
        
            <tr>
                <th colspan="6" style="background-color:#808387; color: white "><center>DETALHES DA MATRICULA</center></th>
            </tr>
            <tr>
                <td><b>AULAS</b></td>
                <td><b>DESCRIÇÃO</b></td>
                <td><b>TIPO</b></td>
                <td><b>ESTADO</b></td>
                <td><b>INICIO</b></td>
                <td><b>FIM</b></td>
            </tr>

            @if($matricula->co_curricular==1)
                @foreach($coCurriculares  as $coCurricular)
                    <tr>
                        <td>{{$coCurricular->disciplina->nome}}</td>
                        <td>{{$coCurricular->disciplina->descricao}}</td>
                        <td>Co Curricular</td>
                        <td>
                            @if($coCurricular->estado==1)
                                Activo
                            @else
                                Cancelado
                            @endif
                        </td>
                        <td>{{$coCurricular->mes->nome}}</td>
                        <td>{{$coCurricular->mesfim->nome}}</td>
                    </tr>
                @endforeach 
            @endif
                    <tr>
                        <td>{{e($matricula->classe->nome)}}</td>
                        <td>{{e($matricula->classe->nome)}}</td>
                        <td>Curricular</td>
                        <td>Activo</td>
                        <td>Fevereiro</td>
                        <td>Novembro</td>
                    </tr>    
    </table>

    
    <table class="table table-bordered" style="font-size: 9pt">
        <tr>
            <th colspan="3" style="background-color:#808387; color: white "><center>FORMA DE PAGAMENTO DA MATRICULA</center></th>
        </tr>
        <tr>
            <td hidden><b>ENTIDADE</b></td>
            <td><b>REFERÊNCIA</b></td>
            <td><b>VALOR TOTAL </b></td>
            <td><b>PRAZO PAG.</b></td>
            <td hidden><b>BANCO</b></td>
        </tr>
        <tr>
            <td hidden>{{$pagamento->entidade}}</td>
            <td>{{$pagamento->referencia}}</td>
            <td>{{ number_format($pagamento->valor, 2, ',', '.') }}</td>
            <td>{{ \Carbon\Carbon::parse($pagamento->prazo_pagamento)->format('d-M-Y') }}</td>
            <td hidden>Millennium BIM</td>
        </tr>
    </table>

    <div>
       <b style="text-align: center;"><center><u>INSTRUÇÃO PARA PAGAMENTO</u></center></b>
    </div>
    
    <p style="text-align: justify; font-size: 8pt;">
        Para proceder ao pagamento das matricula, dirija-se a qualquer ATM do BIM, e procure pela opção PAGAMENTOS,
        e SELECCIONE depois a opção PAGAMENTO DE SERVIÇOS. Digite a ENTIDADE, REFERÊNCIA e o VALOR TOTAL.
        A Matricula poderão também ser paga através do IZI(*181#), Smart IZI, BIM-INTERNET BANKING, ou por
        DEPÓSITO DIRECTO em qualquer BALCÃO do BIM, usando entidade, referência e o valor total exacto constante na sua ficha de propinas.
        Havendo necessidade de confirmação, consulte sua matricula no site ou na Direção Acadêmico da Escola.<br><br>
        <b style="color: red">NB: SÓ SERÃO ACEITES PAGAMENTOS FEITOS NO BIM e DENTRO DE PRAZO PAGAMENTO !</b>
        <br><br>
        <i>Impresso no dia {{ now()->format('d-M-Y H:i') }}</i>
        <img class="right-align1" src="https://millenniumbim.co.mz/media/3465/millenniumbim.svg"  width="100" height="70" alt="Descrição da Imagem">
    </p>


</div>
<div class="footer">
        <div>
             Matriculado por  <i><b>{{$matricula->user->nome}}  </b></i> site <u>{{$matricula->escola->site}}</u>  <b>{{$matricula->escola->slogan}}</b>
                 
        </div>
 </div>

</body>
</html>
