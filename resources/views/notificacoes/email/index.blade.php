<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Yuri admin is super flexible, powerful, clean &amp; modern responsive bootstrap 5 admin template with unlimited possibilities.">
    <meta name="keywords" content="admin template, Yuri admin template, dashboard template, flat admin template, responsive admin template, web app">
    <meta name="author" content="pixelstrap">
    <link rel="icon" href="{{ URL('/public/assets/images/favicon.png') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ URL('/public/assets/images/favicon.png') }}" type="image/x-icon">
    <title>Yuri - Premium Admin Template</title>
    <link href="https://fonts.googleapis.com/css?family=Work+Sans:100,200,300,400,500,600,700,800,900" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Poppins:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <style type="text/css">
      body{
      text-align: center;
      margin: 0 auto;
      width: 650px;
      font-family: Inter , sans-serif;
      background-color: #f6f7fb;
      display: block;
      }
      ul{
      margin:0;
      padding: 0;
      }
      li{
      display: inline-block;
      text-decoration: unset;
      }
      a{
      text-decoration: none;
      }
      p{
      margin: 15px 0;
      }
      h5{
      color:#444;
      text-align:left;
      font-weight:400;
      }
      .text-center{
      text-align: center
      }
      .main-bg-light{
      background-color: #fafafa;
      box-shadow: 0px 0px 14px -4px rgba(0, 0, 0, 0.2705882353); 
      }
      .title{
      color: #444444;
      font-size: 22px;
      font-weight: bold;
      margin-top: 10px;
      margin-bottom: 10px;
      padding-bottom: 0;
      text-transform: uppercase;
      display: inline-block;
      line-height: 1;
      }
      table{
      margin-top:30px
      }
      table.top-0{
      margin-top:0;
      }
      table.order-detail {
      border: 1px solid #ddd;
      border-collapse: collapse;
      }
      table.order-detail tr:nth-child(even) {
      border-top:1px solid #ddd;
      border-bottom:1px solid #ddd;
      }
      table.order-detail tr:nth-child(odd) {
      border-bottom:1px solid #ddd;
      }
      .pad-left-right-space{
      border: unset !important;
      }
      .pad-left-right-space td{
      padding: 5px 15px;
      }
      .pad-left-right-space td p{
      margin: 0;
      }
      .pad-left-right-space td b{
      font-size:15px;
      font-family: 'Inter', sans-serif;
      }
      .order-detail th{
      font-size:16px;
      padding:15px;
      text-align:center;
      background: #fafafa;
      }
      .footer-social-icon tr td img{
      margin-left:5px;
      margin-right:5px;
      }
    </style>
  </head>
  <body style="padding-left: 13%; padding-right: 13%; padding-top: 20px; padding-bottom: 20px; background-color: gray; ">
    <table align="center" border="0" cellpadding="0" cellspacing="0" style="padding: 0 20px;background-color: #fff; -webkit-box-shadow: 0px 0px 14px -4px rgba(0, 0, 0, 0.2705882353);box-shadow: 0px 0px 14px -4px rgba(0, 0, 0, 0.2705882353);width: 100%;">
      <tbody>
        <tr>
          <td>
            <table align="left" border="0" cellpadding="0" cellspacing="0" style="text-align: left;" width="100%">
              <tbody>
                <tr>
                  <td style="text-align: center;"><img src="http://jacintomachava.online/escola/public/logotipo/{{$dados['logotipo']}}" height="110" width="110"  alt="" ></td>
                </tr>
				<tr>
                  <td style="text-align: center;margin-top: -5%;">
					<h4>{{strtoupper($dados['nomeEscola']) }} <br> DIRECÇÃO DE REGISTO ACADÉMICO</h4>
				  </td>
                </tr>
                <tr>
                  <td>
				    <p style="font-size: 14px; text-align: right;"><u><i>Ano Lectivo: <b>2025</b></i></u></p>
                    <p style="font-size: 14px;"><b>Saudações, <br><br> Sr(a)  {{ $dados['nome'] }}</b></p>
                    <p style="font-size: 14px; color:#6C757D;">{{ $dados['corpo'] }}</p>
                  </td>
                </tr>
              </tbody>
            </table>
            <table cellpadding="0" cellspacing="0" border="0" align="left" style="width: 100%;margin-top: 10px;    margin-bottom: 10px;">
              <tbody>
                <tr>
                  <td style="background-color: #fafafa;border: 1px solid #ddd;padding: 15px;letter-spacing: 0.3px;width: 50%;">
                    <h5 style="font-size: 16px; font-weight: 600;color: #000; line-height: 16px; padding-bottom: 13px; border-bottom: 1px solid #e6e8eb; letter-spacing: -0.65px; margin-top:0; margin-bottom: 13px;">Aulas Co-Curriculares</h5>
                    <p style="text-align: left;font-weight: normal; font-size: 14px; color: #000000;line-height: 21px;    margin-top: 0;">
                        1.Aulas de Dança<br> 
                        2.Karatê<br>
                        3.Inglês <br>
                        4.Aulas de Música (Guitarra)
                    </p>
                  </td>
                  <td><img src="http://jacintomachava.online/escola/public/assets/images/email-template/space.jpg" alt=" " height="25" width="30"></td>
                  <td style="background-color: #fafafa;border: 1px solid #ddd;padding: 15px;letter-spacing: 0.3px;width: 50%;">
                    <h5 style="font-size: 16px;font-weight: 600;color: #000; line-height: 16px; padding-bottom: 13px; border-bottom: 1px solid #e6e8eb; letter-spacing: -0.65px; margin-top:0; margin-bottom: 13px;">Endereço & Contactos</h5>
                    <p style="text-align: left;font-weight: normal; font-size: 14px; color: #000000;line-height: 21px;    margin-top: 0;">
                        Moçambique, Maputo<br>
                        {{ $dados['endereco'] }}<br> 
                        {{ $dados['contacto'] }} <br>
                        <a href="https://jacintomachava.online/">www.jacintomachava.online</a>
					</p>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
    </table>
    <table class="main-bg-light text-center top-0" align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
      <tbody>
        <tr>
          <td style="padding: 20px;">
            <div>
              <h4 style="margin:0;text-align: center;">Siga nos nas Redes Sociais</h4>
            </div>
            <table class="footer-social-icon" border="0" cellpadding="0" cellspacing="0" align="center" style="margin-top:20px;">
              <tbody>
                <tr>
                  <td><a href="https://www.facebook.com" target="_blank"><img src="http://jacintomachava.online/escola/public/assets/images/email-template/facebook.png" alt="facebook"></a></td>
                  <td><a href="https://www.youtube.com/" target="_blank"><img src="http://jacintomachava.online/escola/public/assets/images/email-template/youtube.png" alt="youtube"></a></td>
                  <td><a href="https://twitter.com" target="_blank"><img src="http://jacintomachava.online/escola/public/assets/images/email-template/twitter.png" alt="twitter"></a></td>
                  <td><a href="https://accounts.google.com/" target="_blank"><img src="http://jacintomachava.online/escola/public/assets/images/email-template/gplus.png" alt="google account"></a></td>
                  <td><a href="https://www.linkedin.com" target="_blank"><img src="http://jacintomachava.online/escola/public/assets/images/email-template/linkedin.png" alt="linkedin"></a></td>
                  <td><a href="https://in.pinterest.com/" target="_blank"><img src="http://jacintomachava.online/escola/public/assets/images/email-template/pinterest.png" alt="pinterest"></a></td>
                </tr>
              </tbody>
            </table>
            <div style="border-top: 1px solid #ddd; margin: 20px auto 0;"></div>
            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 20px auto 0;">
              <tbody>
                <tr>
                  <td><a href="#!" style="font-size:14px; color:#009DB5; font-weight:600">{{ $dados['slogan'] }}</a></td>
                </tr>
                <tr>
                  <td>
                    <p style="font-size:14px; margin-block:8px;">2024 - {{ $dados['ano'] }} Copy Right, Todos os direitos reservados</p>
                  </td>
                </tr>
                <tr>
                  <td></td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
    </table>
  </body>
</html>