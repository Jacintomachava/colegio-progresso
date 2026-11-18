<div class="sidebar-wrapper" data-sidebar-layout="stroke-svg">
    <div>
    <div class="logo-wrapper"><a href="#">
        <img class="img-fluid" src="{{ URL('/logotipo/'.session('logotipo')) }}" width="100" height="100" alt=""></a>
        <div class="back-btn"><i class="fa fa-angle-left"></i></div>
        <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle" data-feather="grid"> </i></div>
    </div>
    <div class="logo-icon-wrapper"><a href="#">
        <img class="img-fluid" src="{{ URL('/logotipo/'.session('logotipo')) }}"  width="100" height="100" alt=""></a></div>
    <div class="profile-section sidebar-search"> 
        <div class="profile-wrapper">
        <div class="active-profile"> <img class="img-fluid"  src="{{ URL('/avatar/'.Auth::user()->avatar) }}" alt="user">
            <div class="status bg-success"> </div>
        </div>
        <div> 
            <h4>{{ Auth::user()->codigo }}</h4>
            <span>codigo utilizador</span>
        </div>
        </div>
        <div>
        <svg>
            <use href="{{ URL('/assets/svg/icon-sprite.svg#profile-setting') }}"></use>
        </svg>
        </div>
    </div>
    <div class="sidebar-search"> 
        <div class="input-group">
          {{ \Illuminate\Support\Str::limit(session('escola'), 30, '...') }}
        </div>
    </div>
    <nav class="sidebar-main">
        <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
        <div id="sidebar-menu">
        <ul class="sidebar-links" id="simple-bar">
            <li class="back-btn"><a href="index.html"><img class="img-fluid" src="{{ URL('/avatar/'.Auth::user()->avatar) }}" alt=""></a>
            <div class="mobile-back text-end"><span>Back</span><i class="fa fa-angle-right ps-2" aria-hidden="true"></i></div>
            </li>
            <li class="pin-title sidebar-main-title">
            <div> 
                <h6>- Pinned</h6>
            </div>
            </li>

            @role('Secretaria')

                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                    <label class="badge badge-light-primary">1</label><a class="sidebar-link sidebar-title" href="{{route('home.index')}}">
                        <svg class="stroke-icon">
                        <use href="{{ URL('/assets/svg/icon-sprite.svg#stroke-learning') }}"></use>
                        </svg><span class="lan-3">Home</span></a>
                </li>

                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                    <label class="badge badge-light-primary">1</label><a class="sidebar-link sidebar-title" href="{{route('matricula.index')}}">
                        <svg class="stroke-icon">
                        <use href="{{ URL('/assets/svg/icon-sprite.svg#stroke-blog') }}"></use>
                        </svg><span class="lan-3">Matriculas</span></a>
                </li>

                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                    <label class="badge badge-light-primary">1</label><a class="sidebar-link sidebar-title" href="{{ route('progresso.facturas.index') }}">
                        <svg class="stroke-icon">
                        <use href="{{ URL('/assets/svg/icon-sprite.svg#stroke-icons') }}"></use>
                        </svg><span class="lan-3">Facturas</span></a>
                </li>

                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                    <label class="badge badge-light-primary">1</label><a class="sidebar-link sidebar-title" href="{{ route('progresso.recibos.index') }}">
                        <svg class="stroke-icon">
                        <use href="{{ URL('/assets/svg/icon-sprite.svg#stroke-icons') }}"></use>
                        </svg><span class="lan-3">Recibos</span></a>
                </li>

                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                    <label class="badge badge-light-primary">1</label><a class="sidebar-link sidebar-title" href="{{ route('progresso.valores-classes.index') }}">
                        <svg class="stroke-icon">
                        <use href="{{ URL('/assets/svg/icon-sprite.svg#stroke-icons') }}"></use>
                        </svg><span class="lan-3">Valores por Classe</span></a>
                </li>

                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                    <label class="badge badge-light-primary">2</label><a class="sidebar-link sidebar-title" href="#">
                        <svg class="stroke-icon">
                        <use href="{{ URL('/assets/svg/icon-sprite.svg#stroke-learning') }}"></use>
                        </svg><span class="lan-3">Administração</span></a>
                    <ul class="sidebar-submenu">
                        <li><a class="lan-1" href="{{route('funcionario.index')}}">Funcionarios</a></li>
                        <li><a class="lan-2" href="{{route('user.index')}}">Usuarios</a></li>
                    </ul>
                </li>


            @endrole

            @role('Admin')

                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                    <label class="badge badge-light-primary">1</label><a class="sidebar-link sidebar-title" href="{{route('home.index')}}">
                        <svg class="stroke-icon">
                        <use href="{{ URL('/assets/svg/icon-sprite.svg#stroke-learning') }}"></use>
                        </svg><span class="lan-3">Home</span></a>
                </li>

                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                    <label class="badge badge-light-primary">1</label><a class="sidebar-link sidebar-title" href="{{route('matricula.index')}}">
                        <svg class="stroke-icon">
                        <use href="{{ URL('/assets/svg/icon-sprite.svg#stroke-blog') }}"></use>
                        </svg><span class="lan-3">Matriculas</span></a>
                </li>

                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                    <label class="badge badge-light-primary">1</label><a class="sidebar-link sidebar-title" href="{{ route('progresso.facturas.index') }}">
                        <svg class="stroke-icon">
                        <use href="{{ URL('/assets/svg/icon-sprite.svg#stroke-icons') }}"></use>
                        </svg><span class="lan-3">Facturas</span></a>
                </li>

                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                    <label class="badge badge-light-primary">1</label><a class="sidebar-link sidebar-title" href="{{ route('progresso.recibos.index') }}">
                        <svg class="stroke-icon">
                        <use href="{{ URL('/assets/svg/icon-sprite.svg#stroke-icons') }}"></use>
                        </svg><span class="lan-3">Recibos</span></a>
                </li>

                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                    <label class="badge badge-light-primary">1</label><a class="sidebar-link sidebar-title" href="{{ route('progresso.valores-classes.index') }}">
                        <svg class="stroke-icon">
                        <use href="{{ URL('/assets/svg/icon-sprite.svg#stroke-icons') }}"></use>
                        </svg><span class="lan-3">Valores por Classe</span></a>
                </li>

                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                    <label class="badge badge-light-primary">2</label><a class="sidebar-link sidebar-title" href="#">
                        <svg class="stroke-icon">
                        <use href="{{ URL('/assets/svg/icon-sprite.svg#stroke-learning') }}"></use>
                        </svg><span class="lan-3">Administração</span></a>
                    <ul class="sidebar-submenu">
                        <li><a class="lan-1" href="{{route('funcionario.index')}}">Funcionarios</a></li>
                        <li><a class="lan-2" href="{{route('user.index')}}">Usuarios</a></li>
                    </ul>
                </li>

            @endrole


        </ul>
        </div>
        <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
    </nav>
    </div>
</div>