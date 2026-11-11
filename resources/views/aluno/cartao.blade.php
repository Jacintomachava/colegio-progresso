<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Fav and touch icons -->
    <title>ESCOLA</title>
    <!-- Bootstrap core CSS -->
    <link href="/public/assets/bootstrap/css/bootstrap.css" rel="stylesheet">


    <link href="/public/assets/css/style.css" rel="stylesheet">

    <!-- styles needed for carousel slider -->
    <link href="/public/assets/plugins/owl-carousel/owl.carousel.css" rel="stylesheet">
    <link href="/public/assets/plugins/owl-carousel/owl.theme.css" rel="stylesheet">

    <!-- bxSlider CSS file -->
    <link href="/public/assets/plugins/bxslider/jquery.bxslider.css" rel="stylesheet"/>

    <style>
        /**
            Set the margins of the page to 0, so the footer and the header
            can be of the full height and width !
         **/
        @page {
            margin: 0cm 0cm;
        }

        *{
          font-size:10px;
        }



        /** Define the footer rules **/
        footer {
            position: fixed;
            bottom: 0.5cm;
            left: 1cm;
            right: 1cm;
            height: 1cm;

            /** Extra personal styles **/
            text-align: center;
            line-height: 0.8cm;
        }
    </style>


</head>
<body>
<div id="wrapper">

    <div class="main-container">

    <!-- Wrap the content of your PDF inside a main tag -->
        <main style="margin: 30px" >
            <hr>
            <div>
                 <table class="table table-striped table-bordered" id="user_data"  width="100%">
                    <tr>
                        <td  colspan="3" > 
                          <center>
                             <img alt="{{$escola->logotipo}}" src="{{ public_path('logotipo/'.$escola->logotipo) }}" width="42" height="42"><br>
                             <b>{{strtoupper($escola->nome)}}</b>
                          </center>
                          <center>
                            <b>Direção de Registo Acadêmico</b>
                           </center>
                        </td>
                    </tr>
                </table>

                <hr style="border: 1px dotted red;">

                <table class="table table-striped table-bordered" id="user_data"  width="100%">
                    
                    <tr>
                        
                        <tr>
                            <td colspan="2" >
                                
                        </tr>
                        <td> 
                            <b style="text-transform: uppercase;" >{{$aluno->pessoa->nome}}</b><br>
                            
                            <b> {{$aluno->pessoa->tipo_documento}}:</b> {{$aluno->pessoa->numero_documento}} <br> 
                            <b>Ano Ingresso:</b> {{ '20' . substr($aluno->codigo, -2) }} <br>
                            <b>Data Nasc.:</b> {{ \Carbon\Carbon::parse($aluno->pessoa->data_nascimento)->format('d-M-Y')}}  <br>
                            </td>
                            
                        </td>
                        <td > 
                             <b>Codigo:</b> {{$aluno->codigo}}<br>
                             <b>Genero:</b>  {{$aluno->pessoa->genero}}  <br>
                             <b>Provincia:</b> {{$aluno->provincia->nome}}<br>
                             {{$aluno->nome_casa}} <br>

                        </td>

                        <td > 
                            <center>
                               <img alt="company logo" src="{{ public_path('avatar/'.$aluno->avatar) }}"  width="80" height="60" class="thumbnail no-margin">
                            </center>    
                        </td>
                    </tr>

                </table>

                <hr style="border: 1px dotted red;">
    
                   <center>
                        
                                 O(a) Director(a) <br>

                     ----------------------------------
                          
                   </center>   

                  
            </div>
            <hr>

        </main>

    </div>
    <!-- /.main-container -->


</div>
<!-- /.wrapper -->



</body>

</html>
