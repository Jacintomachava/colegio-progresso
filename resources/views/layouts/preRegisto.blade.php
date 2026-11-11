<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Yuri admin is super flexible, powerful, clean &amp; modern responsive bootstrap 5 admin template with unlimited possibilities.">
    <meta name="keywords" content="admin template, Yuri admin template, dashboard template, flat admin template, responsive admin template, web app">
    <meta name="author" content="pixelstrap">
    <link rel="icon" href="../assets/images/favicon.png" type="image/x-icon">
    <link rel="shortcut icon" href="../assets/images/favicon.png" type="image/x-icon">
    <title>Criar Conta</title>
    <!-- Google font-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&amp;family=Nunito+Sans:ital,wght@0,300;0,400;0,700;0,800;0,900;1,700&amp;display=swap" rel="stylesheet">
    <!-- Font Awesome-->
    <link rel="stylesheet" type="text/css" href="{{ URL('/assets/css/font-awesome.css') }}">
    <!-- ico-font-->
    <link rel="stylesheet" type="text/css" href="{{ URL('/assets/css/vendors/icofont.css') }}">
    <!-- Themify icon-->
    <link rel="stylesheet" type="text/css" href="{{ URL('/assets/css/vendors/themify.css') }}">
    <!-- Flag icon-->
    <link rel="stylesheet" type="text/css" href="{{ URL('/assets/css/vendors/flag-icon.css') }}">
    <!-- Feather icon-->
    <link rel="stylesheet" type="text/css" href="{{ URL('/assets/css/vendors/feather-icon.css') }}">
    <!-- Plugins css start-->
    <link rel="stylesheet" type="text/css" href="{{ URL('/assets/css/vendors/slick.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ URL('/assets/css/vendors/slick-theme.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ URL('/assets/css/vendors/scrollbar.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ URL('/assets/css/vendors/animate.css') }}">
    <!-- Plugins css Ends-->
    <!-- Bootstrap css-->
    <link rel="stylesheet" type="text/css" href="{{ URL('/assets/css/vendors/bootstrap.css') }}">
    <!-- App css-->
    <link rel="stylesheet" type="text/css" href="{{ URL('/assets/css/style.css') }}">
    <!-- Responsive css-->
    <link rel="stylesheet" type="text/css" href="{{ URL('/assets/css/responsive.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
      .error {
          color: red;
          margin: 0;
      }
    </style>
    @stack('css')
  </head>
  <body> 
    <!-- loader starts-->
    <div class="loader-wrapper">
      <div class="loader"></div>
    </div>
    <!-- loader ends-->
    <!-- tap on top starts-->
    <div class="tap-top"><i data-feather="chevrons-up"></i></div>
    <!-- tap on tap ends-->
    <!-- page-wrapper Start-->
    <div class="page-wrapper compact-wrapper" id="pageWrapper">
      <div class="page-header row">
        <!-- Page Header Start-->
                <div class="col-auto header-left-wrapper">
                  <div class="header-logo-wrapper p-0 left-header">
                    <div class="logo-wrapper"><a href="index.html"><img class="img-fluid" src="../assets/images/logo/logo_dark.png" alt=""></a></div>
                  </div>
                  <div class="toggle-sidebar">
                    <svg class="status_toggle sidebar-toggle">
                      <use href="../assets/svg/icon-sprite.svg#collapse-sidebar"></use>
                    </svg>
                  </div>
                </div>
        <div class="col-auto header-right-wrapper page-title">
          <div>
            <h2>Pré-Registo</h2>
            <nav>
              <ol class="breadcrumb justify-content-sm-start align-items-center mb-0">
                <li class="breadcrumb-item"><a href="{{route('conta.associacao')}}">Associação</a></li>
                <li class="breadcrumb-item f-w-500"><a href="{{route('conta.clube')}}">Clube</a></li>
                <li class="breadcrumb-item f-w-500 active"><a href="{{route('conta.arbitro')}}">Arbitro</a></li>
              </ol>
            </nav>
          </div>
        </div>
                <div class="col header-wrapper m-0 header-right-wrapper">
                  <div class="row m-0">
                    <form class="form-inline search-full col" action="#" method="get">
                      <div class="form-group w-100">
                        <div class="Typeahead Typeahead--twitterUsers">
                          <div class="u-posRelative">
                            <input class="demo-input Typeahead-input form-control-plaintext w-100" type="text" placeholder="Search anything .." name="q" title="" autofocus>
                            <div class="spinner-border Typeahead-spinner" role="status"><span class="sr-only">Loading...</span></div><i class="close-search" data-feather="x"></i>
                          </div>
                          <div class="Typeahead-menu"></div>
                        </div>
                      </div>
                    </form>
                    <div class="header-logo-wrapper col-auto p-0 left-header"></div>
                    <div class="nav-right col-auto pull-right right-header p-0 ms-auto">
                      <ul class="nav-menus">

                        <li class="profile-nav onhover-dropdown">
                          <div class="onhover-click">
                            <div class="sidebar-image"> <img src="../assets/images/user.png" alt="profile"><span class="status status-success"></span></div>
                            <div class="sidebar-content"> 
                              <h4>Pré-Registo</h4><span class="f-12 f-w-600 f-light">Federação</span>
                            </div>
                          </div>
                          <ul class="profile-dropdown onhover-show-div">
                            <li><a href="sign-up.html">
                                <div class="profile-icon">
                                  <svg>
                                    <use href="../assets/svg/icon-sprite.svg#user"></use>
                                  </svg>
                                </div><span>Account </span></a></li>
                            <li><a href="letter-box.html">
                                <div class="profile-icon">
                                  <svg>
                                    <use href="../assets/svg/icon-sprite.svg#stroke-email"></use>
                                  </svg>
                                </div><span>Inbox</span></a></li>
                            <li><a href="task.html">
                                <div class="profile-icon">
                                  <svg>
                                    <use href="../assets/svg/icon-sprite.svg#notepad"></use>
                                  </svg>
                                </div><span>Taskboard</span></a></li>
                            <li><a href="edit-profile.html">
                                <div class="profile-icon">
                                  <svg>
                                    <use href="../assets/svg/icon-sprite.svg#settings"></use>
                                  </svg>
                                </div><span>Settings</span></a></li>
                            <li><a href="login.html">
                                <div class="profile-icon">
                                  <svg>
                                    <use href="../assets/svg/icon-sprite.svg#login"></use>
                                  </svg>
                                </div><span>Log out</span></a></li>
                          </ul>
                        </li>
                      </ul>
                    </div>
                    <script class="result-template" type="text/x-handlebars-template">
                      <div class="ProfileCard u-cf">                        
                      <div class="ProfileCard-avatar"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-airplay m-0"><path d="M5 17H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-1"></path><polygon points="12 15 17 21 7 21 12 15"></polygon></svg></div>
                      <div class="ProfileCard-details">
                      <div class="ProfileCard-realName"></div>
                      </div>
                      </div>
                    </script>
                    <script class="empty-template" type="text/x-handlebars-template"><div class="EmptyMessage">Your search turned up 0 results. This most likely means the backend is down, yikes!</div></script>
                  </div>
                </div>
                <!-- Page Header Ends-->
      </div>
      <!-- Page Body Start-->
      <div class="page-body-wrapper">
        <!-- Page Sidebar Start-->
        <div class="sidebar-wrapper" data-sidebar-layout="stroke-svg">
          <div>
            <div class="logo-wrapper"><a href="index.html"><img class="img-fluid" src="../assets/images/logo/logo_dark.png" alt=""></a>
              <div class="back-btn"><i class="fa fa-angle-left"></i></div>
              <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle" data-feather="grid"> </i></div>
            </div>
            <div class="logo-icon-wrapper"><a href="index.html"><img class="img-fluid" src="../assets/images/logo/logo-icon.png" alt=""></a></div>
            <div class="profile-section sidebar-search"> 
              <div class="profile-wrapper">
                <div class="active-profile"> <img class="img-fluid" src="../assets/images/user.png" alt="user">
                  <div class="status bg-success"> </div>
                </div>
                <div> 
                  <h4>Federação</h4><span>Basquetebol</span>
                </div>
              </div>
              <div>
                <svg>
                  <use href="../assets/svg/icon-sprite.svg#profile-setting"></use>
                </svg>
              </div>
            </div>
            <div class="sidebar-search"> 
              <div class="input-group">
                  Federação de Basquetebol - FMB
              </div>
            </div>
            <nav class="sidebar-main">
              <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
              <div id="sidebar-menu">
                <ul class="sidebar-links" id="simple-bar">
                  <li class="back-btn"><a href="index.html"><img class="img-fluid" src="../assets/images/logo/logo-icon.png" alt=""></a>
                    <div class="mobile-back text-end"><span>Back</span><i class="fa fa-angle-right ps-2" aria-hidden="true"></i></div>
                  </li>
                  <li class="pin-title sidebar-main-title">
                    <div> 
                      <h6>- Pinned</h6>
                    </div>
                  </li>

                  <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                    <a class="sidebar-link sidebar-title link-nav" href="{{route('conta.associacao')}}">
                      <svg class="stroke-icon">
                        <use href="../assets/svg/icon-sprite.svg#stroke-landing-page"></use>
                      </svg><span>Associação</span>
                    </a>
                  </li>
                  <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                    <a class="sidebar-link sidebar-title link-nav" href="{{route('conta.clube')}}">
                      <svg class="stroke-icon">
                        <use href="../assets/svg/icon-sprite.svg#stroke-sample-page"></use>
                      </svg><span>Clube</span>
                    </a>
                  </li>
                  <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                   <a class="sidebar-link sidebar-title link-nav" href="{{route('conta.arbitro')}}">
                      <svg class="stroke-icon">
                        <use href="../assets/svg/icon-sprite.svg#stroke-internationalization"></use>
                      </svg><span>Arbitro</span>
                    </a>
                  </li>
                </ul>
              </div>
              <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
            </nav>
          </div>
        </div>
        <!-- Page Sidebar Ends-->
        <div class="page-body">
          <!-- Container-fluid starts-->
          <div class="container-fluid">

               @yield('conteudo')
               
          </div>
        </div>
        <!-- footer start-->
        <footer class="footer">
          <div class="container-fluid">
            <div class="row gy-1">
              <div class="col-lg-6 col-md-7 footer-copyright">
                <p class="mb-0 f-light f-w-500">Copyright 2024 © Yuri theme by pixelstrap</p>
              </div>
              <div class="col-lg-6 col-md-5">
                <div class="d-flex">
                  <ul class="footer-language">
                    <li class="language-nav">
                      <div class="translate_wrapper">
                        <div class="current_lang">
                          <div class="lang"><span class="lang-txt f-light f-w-500">English</span><span class="ms-2"> <i class="fa fa-caret-down txt-primary">          </i></span></div>
                        </div>
                        <div class="more_lang">
                          <div class="lang selected" data-value="English"><span class="lang-txt">English<span> (US)</span></span></div>
                          <div class="lang" data-value="Deutsch"><span class="lang-txt">Deutsch</span></div>
                          <div class="lang" data-value="Español"><span class="lang-txt">Español</span></div>
                          <div class="lang" data-value="Français"><span class="lang-txt">Français</span></div>
                          <div class="lang" data-value="Português"><span class="lang-txt">Português<span> (BR)</span></span></div>
                          <div class="lang" data-value="简体中文"><span class="lang-txt">简体中文</span></div>
                          <div class="lang" data-value="لعربية"><span class="lang-txt">لعربية <span> (ae)  </span></span></div>
                        </div>
                      </div>
                    </li>
                  </ul>
                  <div class="lang-title f-light f-w-500"><i class="me-1" data-feather="globe"> </i><span>Select Region</span></div>
                </div>
              </div>
            </div>
          </div>
        </footer>
      </div>
    </div>
    <!-- latest jquery-->
    <script src="{{ URL('/assets/js/jquery.min.js') }}"></script>
    <!-- js Validate -->
    <script src="{{URL('/assets/js/jquery.validate.js')}}"></script>
    <!-- Bootstrap js-->
    <script src="{{ URL('/assets/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <!-- feather icon js-->
    <script src="{{ URL('/assets/js/icons/feather-icon/feather.min.js') }}"></script>
    <script src="{{ URL('/assets/js/icons/feather-icon/feather-icon.js') }}"></script>
    <!-- scrollbar js-->
    <script src="{{ URL('/assets/js/scrollbar/simplebar.js') }}"></script>
    <script src="{{ URL('/assets/js/scrollbar/custom.js') }}"></script>
    <!-- Sidebar jquery-->
    <script src="{{ URL('/assets/js/config.js') }}"></script>
    <!-- Plugins JS start-->
    <script src="{{ URL('/assets/js/sidebar-menu.js') }}"></script>
    <script src="{{ URL('/assets/js/sidebar-pin.js') }}"></script>
    <script src="{{ URL('/assets/js/slick/slick.min.js') }}"></script>
    <script src="{{ URL('/assets/js/slick/slick.js') }}"></script>
    <script src="{{ URL('/assets/js/header-slick.js') }}"></script>
    <script src="{{ URL('/assets/js/form-wizard/form-wizard.js') }}"></script>
    <script src="{{ URL('/assets/js/form-wizard/image-upload.js') }}"></script>
    <script src="{{ URL('/assets/js/height-equal.js') }}"></script>
    <!-- Plugins JS Ends-->
    <!-- Theme js-->
    <script src="{{ URL('/assets/js/script.js') }}"></script>
    <script src="{{ URL('/assets/js/script2.js') }}"></script>
    <script src="{{ URL('/assets/js/theme-customizer/customizer.js') }}"></script>
    @stack('js')
  </body>
</html>