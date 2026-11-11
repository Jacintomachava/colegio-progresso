
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pauta de Exame de {{$classe->nome}} ano {{$anoAcademico->ano}}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 5px;
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
    <b>Exame da {{$classe->nome}}</b>
</div>
<div class="content">

    @php
        use Illuminate\Support\Str;
    @endphp


    <b style="font-size:10pt ; ">Director(a) da Escola:______________________________________________</b>
    <div class="card-block row">
              <div class="col-sm-12 col-lg-12 col-xl-12">
                  <div class="table-responsive custom-scrollbar">
                      <table class="table table-bordered" id="tabelas-notas">
                        <thead style="text-align:center;">
                            <tr>
                                <th  rowspan="3" style="border: 0.2px solid black;  width: 2%;" >Nº</th>
                                <th  rowspan="3" style="border: 0.2px solid black;  width: 8%; ">Nome</th>
                                <th  rowspan="3" style="border: 0.2px solid black;  width: 3%;">G</th>
                                <th  rowspan="3" style="border: 0.2px solid black;  width: 3%;">Prov.</th>

                                {{-- Cabeçalho das disciplinas --}}
                                @foreach ($disciplinasComExame as $d)
                                    <th colspan="5" style="border: 0.2px solid black;  ">{{ $d->disciplina->nome }}</th>
                                @endforeach

                                <th rowspan="3" style="border: 0.2px solid black;  width: 3%;">M F</th>
                                <th rowspan="3" style="border: 0.2px solid black; width: 5.5%; ">Resultado</th>
                            </tr>
                            <tr>
                                @foreach ($disciplinasComExame as $d)
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; width: 3%;">NCD</th>
                                    <th scope="col" colspan="2" style="border: 0.2px solid black; width: 3%;">Exame</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; width: 3%;">Voto</th>
                                    <th scope="col" rowspan="2" style="border: 0.2px solid black; width: 3%;">NF</th>
                                @endforeach
                            </tr>
                            <tr>
                                @foreach ($disciplinasComExame as $d)
                                    <th scope="col"  style="border: 0.2px solid black; width: 3%">1ª ch.</th>
                                    <th scope="col"  style="border: 0.2px solid black; width: 3%;">2ª ch.</th>
                                @endforeach
                            </tr>

                        </thead>

                        <tbody style="text-align:center;">
                            @foreach($alunos as $mat)
                                @php
                                    $aluno = $mat->aluno;

                                    // PX base do aluno (qualquer disciplina)
                                    $pxBase = $pautasExame->firstWhere('aluno_id', $aluno->id);

                                    // Obter todas notas final do aluno
                                    $nfs = [];
                                    

                                    foreach ($disciplinasComExame as $disc) {
                                        $px = $pautasExame->where('aluno_id', $aluno->id)
                                                        ->where('disciplina_id', $disc->disciplina_id)
                                                        ->first();

                                        $nfs[] = $px->nf ?? 0;
                                    }

                                    // Média final = soma das NFs / nº disciplinas
                                    $mediaFinal = count($nfs) > 0 ? round(array_sum($nfs) / count($nfs), 2) : 0;

                                    $resultado = $mediaFinal >= 9.5 ? 'Aprovado' : 'Reprovado';
                                @endphp

                                <tr>
                                    <td style="border: 0.2px solid black; width: 0.1%; ">{{ $pxBase?->numero ?? '' }}</td>
                                    <td style="border: 0.2px solid black; width: 0.1%; text-align:left; ">{{ $aluno->pessoa->nome }}</td>
                                    <td style="border: 0.2px solid black; width: 0.1%; ">{{ substr($aluno->pessoa->genero, 0, 1) }}</td>
                                    <td style="border: 0.2px solid black; width: 0.1%; ">{{ $pxBase?->proveniencia ?? '' }}</td>

                                    {{-- Notas por disciplina --}}
                                    @foreach($disciplinasComExame as $disc)
                                        @php
                                            $px = $pautasExame->where('aluno_id', $aluno->id)
                                                            ->where('disciplina_id', $disc->disciplina_id)
                                                            ->first();
                                        @endphp
                                        <td>{{ $px->ncd == 0 ? '' : $px->ncd }}</td>
                                        <td>{{ $px->primeira_chamada==0 ? '': $px->primeira_chamada }}</td>
                                        <td>{{ $px->segunda_chamada==0 ? '': $px->segunda_chamada }}</td>
                                        <td>{{ $px->voto==0 ? '':$px->voto }}</td>
                                        <td style="text-align: center;">
                                            {{ $px->nf==0? '':$px->nf }}
                                        </td>
                                    @endforeach

                                    <td style="font-weight: bold; text-align:center;">
                                        {{ $mediaFinal==0? '': $mediaFinal }}
                                    </td>

                                    <td style="font-weight: bold; color: {{ $resultado == 'Aprovado' ? 'green' : 'red' }}">
                                        {{ $mediaFinal==0? '': $resultado }}
                                    </td>
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
            _________________________________<br>
            _____/_____/ {{$anoAcademico->ano}}
        </td>
        <td>
            O(a) Professor(a) <br>
            _________________________________<br>
            _____/_____/ {{$anoAcademico->ano}}
        </td>
        <td>
            O(a) Professor(a) <br>
            _________________________________<br>
            _____/_____/ {{$anoAcademico->ano}}
        </td>
        <td>
            O(a) Professor(a) <br>
            _________________________________<br>
            _____/_____/ {{$anoAcademico->ano}}
        </td>
        <td>
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
