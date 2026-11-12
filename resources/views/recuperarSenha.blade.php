<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Yuri admin is super flexible, powerful, clean &amp; modern responsive bootstrap 5 admin template with unlimited possibilities.">
    <meta name="keywords" content="admin template, Yuri admin template, dashboard template, flat admin template, responsive admin template, web app">
    <meta name="author" content="pixelstrap">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" href="{{ URL('/public/assets/images/logo/logotipo.jpg')}}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ URL('/public/assets/images/logo/logotipo.jpg')}}" type="image/x-icon">
    <title>Login</title>
    <!-- Google font-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&amp;family=Nunito+Sans:ital,wght@0,300;0,400;0,700;0,800;0,900;1,700&amp;display=swap" rel="stylesheet">
    <!-- Font Awesome-->
    <link rel="stylesheet" type="text/css" href="{{ URL('/assets/css/font-awesome.css')}}">
    <!-- ico-font-->
    <link rel="stylesheet" type="text/css" href="{{ URL('/assets/css/vendors/icofont.css')}}">
    <!-- Themify icon-->
    <link rel="stylesheet" type="text/css" href="{{ URL('/assets/css/vendors/themify.css')}}">
    <!-- Flag icon-->
    <link rel="stylesheet" type="text/css" href="{{ URL('/assets/css/vendors/flag-icon.css')}}">
    <!-- Feather icon-->
    <link rel="stylesheet" type="text/css" href="{{ URL('/assets/css/vendors/feather-icon.css')}}">
    <!-- Plugins css start-->
    <!-- Plugins css Ends-->
    <!-- Bootstrap css-->
    <link rel="stylesheet" type="text/css" href="{{ URL('/assets/css/vendors/bootstrap.css')}}">
    <!-- App css-->
    <link rel="stylesheet" type="text/css" href="{{ URL('/assets/css/style.css')}}">
    <!-- Responsive css-->
    <link rel="stylesheet" type="text/css" href="{{ URL('/assets/css/responsive.css')}}">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
      .error {
          color: red;
          margin: 0;
      }
    </style>


  </head>
  <body>
    <!-- login page start-->
    <div class="container-fluid">
      <div class="row">
        <div class="col-xl-5">
             <img class="bg-img-cover bg-center" src="{{ URL('/images/login.JPEG')}}" alt="looginpage">
        </div>
        <div class="col-xl-7 p-0">
          <div class="login-card login-dark">
            <div>
              <div class="login-main"> 
                <form class="theme-form" id="formulario">
                  <center>
                    <img  src="{{ URL('/logotipo/progresso.png') }}" width="150" height="150" alt="looginpage">
                  </center>

                  @csrf

                  <div class="col-12">
                      <small style="color: blue">
                        <br>Se for aluno coloca codigo aluno, vai receber nova senha no telefone de encarragdo associado ao aluno, se for funcionario, professor usa telefone registado
                        <br><br>
                      </small>
                  </div>

                  <div class="col-12">
                    <label class="form-label">Telefone ou Codigo Aluno</label>
                    <input class="form-control" name="telefone" type="text" placeholder="Telefone ou Codigo Aluno ">
                  </div>

                  <div class="col-12">
                      <div class="error" id="error" style="color: red; text-align: center">
                      </div>
                  </div>

                  <div class="form-group mb-0">
                    <div class="checkbox p-0">
                      <input id="checkbox1" type="checkbox">
                      <label class="text-muted" for="checkbox1">Lembrar Senha</label>
                    </div><a class="link" href="{{route('logar')}}">Login?</a>
                    <div class="text-end mt-3">
                        <button id="botao_salvar" type="submit" class="btn btn-success w-100">
                            <span id="botao_texto">{{__('Recuperar Senha')}}</span>
                            <i id="icon_enviar" class="ri-arrow-right-line label-icon align-middle fs-16 ms-2"></i>
                        </button>
                    </div>
                  </div>
                  <br>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- latest jquery-->
      <script src="{{ URL('/assets/js/jquery.min.js')}}"></script>

      <!-- js Validate -->
      <script src="{{URL('/assets/js/jquery.validate.js')}}"></script>
      <!-- Bootstrap js-->
      <script src="{{ URL('/assets/js/bootstrap/bootstrap.bundle.min.js')}}"></script>
      <!-- feather icon js-->
      <script src="{{ URL('/assets/js/icons/feather-icon/feather.min.js')}}"></script>
      <script src="{{ URL('/assets/js/icons/feather-icon/feather-icon.js')}}"></script>
      <!-- scrollbar js-->
      <!-- Sidebar jquery-->
      <script src="{{ URL('/assets/js/config.js')}}"></script>
      <!-- Plugins JS start-->
      <!-- Plugins JS Ends-->
      <!-- Theme js-->
      <script src="{{ URL('/assets/js/script.js')}}"></script>

      <script>

        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $("#formulario").validate({
                // Adicionar regras para cada campo
                rules: {
                    telefone: {
                        required: true,
                        minlength: 9,
                        minlength: 9
                    }
                },
                submitHandler: function(form) {
                    $.ajax({
                        type: "POST",
                        url: "{{ route('recuperarSenha.telefone') }}",
                        data: $(form).serialize(), // Corrigido para usar `form` em vez de `this`

                        beforeSend: function () {
                            // Desabilita o botão de envio e altera o ícone para mostrar que está autenticando
                            $('#botao_salvar').attr('disabled', true);
                            $('#icon_enviar').removeClass('ri-arrow-right-line').addClass('spinner-border ri-loader-2-line');
                            $('#botao_texto').text('Recuperar Senha...');
                        },

                        success: function(response) {
                            // Habilita o botão e retorna o ícone original
                            $('#botao_salvar').attr('disabled', false);
                            $('#icon_enviar').removeClass('spinner-border ri-loader-2-line').addClass('ri-arrow-right-line');
                            $('#botao_texto').text('Recuperar Senha');

                            // Redireciona ou exibe uma mensagem de erro com base na resposta
                            if(response.status == 1) {

                               Swal.fire({
                                  icon: 'success',
                                  title: 'Sucesso!',
                                  text: response.message,
                              });

                              window.location.href = '/logar';
                                
                            } else if(response.status == 0) {
                                 Swal.fire({
                                    icon: 'error',
                                    title: 'Erro!',
                                    text: response.message,
                                });
                            }
                        },
                        error: function(errors) {
                            // Habilita o botão e retorna o ícone original em caso de erro
                            $('#botao_salvar').attr('disabled', false);
                            $('#icon_enviar').removeClass('spinner-border ri-loader-2-line').addClass('ri-arrow-right-line');
                            $('#botao_texto').text('Recuperar Senha');

                            // Exibe a mensagem de erro
                            var resultado = document.getElementById('error');
                            resultado.innerText = errors.responseJSON.message;

                        }
                    });
                }
            });
        });

      </script>


    </div>
  </body>
</html>