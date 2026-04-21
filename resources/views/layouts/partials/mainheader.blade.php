<header class="main-header">
    <a href="{{ url('/home') }}" class="logo">
        <span class="logo-mini">QUTY Karunia</span>
        <span class="logo-lg"><b>QUTY</b> Karunia</span>
    </a>
    <nav class="navbar navbar-static-top" role="navigation">
        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button" aria-label="Toggle navigation">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </a>
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                @auth
                <li class="nav-search-item">
                    <form class="navbar-form">
                        <div class="enhanced-search">
                            <div class="input-group">
                                <input type="text" id="global-search" class="form-control" placeholder="Search (Ctrl+K)...">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-flat" aria-label="Search">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </span>
                            </div>
                        </div>
                    </form>
                </li>
                @endauth

                {{-- Notifications Bell Icon (Dropdown created by notification-ui.js) --}}
                @auth
                @endauth

                @auth
                    <li class="nav-theme-item nav-theme-item-compact">
                        <button type="button" class="btn btn-default btn-sm" data-theme-toggle aria-label="Theme Toggle">
                            <i class="fa fa-moon-o" data-theme-icon></i>
                            <span data-theme-label>Dark Mode</span>
                        </button>
                    </li>
                @endauth

                @if (Auth::guest())
                    <li><a href="{{ url('/login') }}">Login</a></li>
                @else
                    <li class="dropdown user user-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            @if(Auth::user()->profile_picture)
                                <img src="{{ asset('storage/' . Auth::user()->profile_picture) }}" class="user-image" alt="User Image">
                            @else
                                <img src="{{ asset('img/default-avatar.png') }}" class="user-image" alt="User Image">
                            @endif
                            <span class="hidden-xs">{{ Auth::user()->name }}</span>
                            <i class="fa fa-angle-down nav-user-chevron"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <!-- User image -->
                            <li class="user-header">
                                @if(Auth::user()->profile_picture)
                                    <img src="{{ asset('storage/' . Auth::user()->profile_picture) }}" class="img-circle" alt="User Image">
                                @else
                                    <img src="{{ asset('img/default-avatar.png') }}" class="img-circle" alt="User Image">
                                @endif
                                <p>
                                    {{ Auth::user()->name }}
                                    <small>{{ Auth::user()->email }}</small>
                                    <small>Member since {{ Auth::user()->created_at->format('M Y') }}</small>
                                </p>
                            </li>
                            <!-- Menu Body -->
                            <li class="user-body">
                                <div class="row">
                                    <div class="col-xs-12 text-center">
                                        <a href="{{ route('profile.edit') }}" class="btn btn-default btn-flat btn-sm">
                                            <i class="fa fa-user"></i> My Profile
                                        </a>
                                    </div>
                                </div>
                            </li>
                            <!-- Menu Footer-->
                            <li class="user-footer">
                                <div class="pull-left">
                                    <a href="{{ route('profile.edit') }}" class="btn btn-default btn-flat">
                                        <i class="fa fa-cog"></i> Settings
                                    </a>
                                </div>
                                <div class="pull-right">
                                    <form method="POST" action="{{ route('logout') }}" class="inline-logout-form">
                                        @csrf
                                        <button type="submit" class="btn btn-default btn-flat">
                                            <i class="fa fa-sign-out"></i> Sign out
                                        </button>
                                    </form>
                                </div>
                            </li>
                        </ul>
                    </li>
                @endif
            </ul>
        </div>
    </nav>
</header>