<!DOCTYPE html>
<html lang="pt">
<head>
    @include('includes.leading.css')
    <style>
        .lh-footer {
            background: #073b32;
            color: rgba(255, 255, 255, .82);
        }

        .lh-footer-top {
            padding: 58px 0 34px;
        }

        .lh-footer-grid {
            display: grid;
            grid-template-columns: minmax(240px, 1.35fr) minmax(160px, .8fr) minmax(180px, .9fr) minmax(240px, 1.2fr);
            gap: 34px;
        }

        .lh-footer-logo img {
            max-width: 132px;
            height: auto;
            display: block;
            background: #ffffff;
            padding: 8px;
            border-radius: 8px;
            margin-bottom: 18px;
        }

        .lh-footer h4 {
            color: #ffffff;
            font-size: 17px;
            font-weight: 800;
            margin: 0 0 16px;
        }

        .lh-footer p {
            margin: 0;
            line-height: 1.75;
        }

        .lh-footer ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            gap: 10px;
        }

        .lh-footer a {
            color: rgba(255, 255, 255, .82);
        }

        .lh-footer a:hover {
            color: #f7b719;
            text-decoration: none;
        }

        .lh-footer-contact li {
            display: grid;
            grid-template-columns: 28px 1fr;
            gap: 10px;
            line-height: 1.55;
        }

        .lh-footer-contact i {
            color: #f7b719;
            margin-top: 4px;
        }

        .lh-footer-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 18px;
        }

        .lh-footer-badges span {
            display: inline-flex;
            align-items: center;
            min-height: 30px;
            padding: 0 10px;
            border-radius: 8px;
            background: rgba(255, 255, 255, .1);
            color: #ffffff;
            font-size: 12px;
            font-weight: 800;
        }

        .lh-footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, .14);
            padding: 16px 0;
        }

        .lh-footer-bottom-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            font-size: 13px;
        }

        .lh-footer-bottom-links {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        @media (max-width: 991px) {
            .lh-footer-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 640px) {
            .lh-footer-grid {
                grid-template-columns: 1fr;
            }

            .lh-footer-bottom-row {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>
<body id="bg">
<div class="page-wraper">
    <div id="loading-area"></div>
    <header class="site-header header-style-4 mo-left">
        <div class="top-bar">
            <div class="container">
                <div class="row justify-content-between">
                    <div class="dez-topbar-left">
                        <ul class="social-line text-center pull-right">
                            <li>
                                <a href="https://www.google.com/maps/place/Col%C3%A9gio+Lhaysso+Kindergarten+and+School/@-25.8603049,32.6127434,13z/data=!4m10!1m2!2m1!1slhaysso!3m6!1s0x1ee6918def2c9adf:0xcc5fca9142c4f5f5!8m2!3d-25.8597861!4d32.6262577!15sCgdsaGF5c3NvkgEYZ2VuZXJhbF9lZHVjYXRpb25fc2Nob29sqgE_Cg0vZy8xMXg3NDZwNHRwEAEyHxABIhtTuCLX1NAEdxB-zm0_OVJeu4QujK-BRtHtfaUyCxACIgdsaGF5c3Nv4AEA!16s%2Fg%2F11x746p4tp?entry=ttu&g_ep=EgoyMDI1MDgwNi4wIKXMDSoASAFQAw%3D%3D" target="_blank" rel="noopener noreferrer">
                                    <i class="fa fa-map-marker"></i>
                                    <span>Av. Cardeal Dom Alexandre, Bairro das Mahotas, Cidade de Maputo.</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="dez-topbar-right">
                        <ul class="social-line text-center pull-right"></ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="sticky-header main-bar-wraper navbar-expand-lg">
            <div class="main-bar clearfix">
                <div class="container header-contant-block">
                    <div class="row">
                        <div class="col-xl-4 col-lg-3 d-flex">
                            <div class="align-self-center">
                                <a href="{{ route('raiz') }}#inicio">
                                    <img src="{{ URL('/leading/images/logotipo.png') }}" width="100" height="40%" alt="Colégio Lhaysso">
                                </a>
                            </div>
                        </div>
                        <div class="col-xl-8 col-lg-9">
                            <ul class="contact-info clearfix">
                                <li>
                                    <h6 class="text-primary"><i class="fa fa-phone"></i> Contacto</h6>
                                    <span>+258 878403247</span>
                                </li>
                                <li>
                                    <h6 class="text-primary"><i class="fa fa-envelope-o"></i> WhatsApp</h6>
                                    <a href="https://wa.me/258878403247" target="_blank" rel="noopener noreferrer">
                                        <i class="fa fa-whatsapp"></i> 878403247
                                    </a>
                                </li>
                                <li>
                                    <h6 class="text-primary"><i class="fa fa-clock-o"></i> Horário</h6>
                                    <span>Seg - Sex: 8:00 - 17:00</span>
                                </li>
                                <li>
                                    <a href="{{ route('logar') }}" class="site-button m-r15 text-white text-center btn-block">
                                        <h5 class="m-a0">Área Restrita</h5>
                                        <p class="m-a0">Entrar no Sistema</p>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="slide-up">
                    <div class="container clearfix bg-primary">
                        <div class="logo-header mostion">
                            <a href="{{ route('raiz') }}#inicio">
                                <img src="{{ URL('/leading/images/logotipo.png') }}" width="30%" height="30%" alt="Colégio Lhaysso">
                            </a>
                        </div>
                        <button class="navbar-toggler collapsed navicon justify-content-end" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Abrir navegação">
                            <span></span>
                            <span></span>
                            <span></span>
                        </button>
                        @include('includes.leading.menu')
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="page-content">
        @yield('conteudo')
    </div>

    <footer class="lh-footer">
        <div class="lh-footer-top">
            <div class="container">
                <div class="lh-footer-grid">
                    <div>
                        <a class="lh-footer-logo" href="{{ route('raiz') }}#inicio">
                            <img src="{{ URL('/leading/images/logotipo.png') }}" alt="Colégio Lhaysso">
                        </a>
                        <h4>Colégio Lhaysso Kindergarten and School</h4>
                        <p>Uma escola de iniciativa privada dedicada à formação integral, ao acompanhamento próximo e ao desenvolvimento de cada aluno.</p>
                        <div class="lh-footer-badges">
                            <span>Meu filho, meu tesouro</span>
                            <span>1ª à 9ª classe</span>
                            <span>Maputo</span>
                        </div>
                    </div>

                    <div>
                        <h4>Navegação</h4>
                        <ul>
                            <li><a href="{{ route('raiz') }}#sobre">Sobre o colégio</a></li>
                            <li><a href="{{ route('raiz') }}#servicos">Serviços escolares</a></li>
                            <li><a href="{{ route('raiz') }}#ensino">Oferta educativa</a></li>
                            <li><a href="{{ route('raiz') }}#galeria">Galeria</a></li>
                        </ul>
                    </div>

                    <div>
                        <h4>Informações</h4>
                        <ul>
                            <li><a href="{{ route('raiz') }}#admissao">Admissão</a></li>
                            <li><a href="{{ route('raiz') }}#matricula-info">Matrícula e propina</a></li>
                            <li><a href="{{ route('raiz') }}#regulamento">Regulamento interno</a></li>
                            <li><a href="{{ route('logar') }}">Área restrita</a></li>
                        </ul>
                    </div>

                    <div>
                        <h4>Contactos</h4>
                        <ul class="lh-footer-contact">
                            <li><i class="fa fa-map-marker"></i><span>Av. Cardeal Dom Alexandre, Bloco 2, Quarteirão nº 22, Distrito Municipal KaMavota, Bairro das Mahotas, Cidade de Maputo.</span></li>
                            <li><i class="fa fa-phone"></i><span><a href="tel:+258878403247">+258 878403247</a></span></li>
                            <li><i class="fa fa-whatsapp"></i><span><a href="https://wa.me/258878403247" target="_blank" rel="noopener noreferrer">WhatsApp 878403247</a></span></li>
                            <li><i class="fa fa-envelope"></i><span><a href="mailto:lhayssokkindergarden@gmail.com">lhayssokkindergarden@gmail.com</a></span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="lh-footer-bottom">
            <div class="container">
                <div class="lh-footer-bottom-row">
                    <span>© <span class="current-year"></span> Colégio Lhaysso Kindergarten and School. Todos os direitos reservados.</span>
                    <div class="lh-footer-bottom-links">
                        <a href="{{ route('politicas') }}">Política de Privacidade</a>
                        <a href="{{ route('termos') }}">Termos de Serviço</a>
                        <a href="{{ route('raiz') }}#contacto">Central de Ajuda</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <button class="scroltop fa fa-arrow-up"></button>
</div>

@include('includes.leading.js')
</body>
</html>
