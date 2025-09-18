
    <header id="page-topbar">
        <div class="layout-width">
            <div class="navbar-header">

                <div class="d-flex">
                    <!-- LOGO -->
                    <div class="navbar-brand-box horizontal-logo">
                        <a href="index.html" class="logo logo-dark">
                            <span class="logo-sm">
                                <img src="assets/images/logo-sm.png" alt="" height="22">
                            </span>
                            <span class="logo-lg">
                                <img src="assets/images/logo-dark.png" alt="" height="17">
                            </span>
                        </a>

                        <a href="index.html" class="logo logo-light">
                            <span class="logo-sm">
                                <img src="assets/images/logo-sm.png" alt="" height="22">
                            </span>
                            <span class="logo-lg">
                                <img src="assets/images/logo-light.png" alt="" height="17">
                            </span>
                        </a>
                    </div>

                    <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger" id="topnav-hamburger-icon">
                        <span class="hamburger-icon">
                            <span></span>
                            <span></span>
                            <span></span>
                        </span>
                    </button>

                    @if( request()->is('daily-input'))
                        <div class="d-flex align-items-center ms-2 mt-1">
                            <div class="input-page">
                                <h4>Daily Input</h4>
                            </div>
                        </div>
                    @endif

                </div>
                
                <div class="d-flex align-items-center">
                    <div class=" ms-auto">
                        <!-- Add 'Create Prep Work Order' and 'Create Daily Input' links here -->
                        <a href="{{ route('prep-orders.index') }}" class="btn btn-sm btn-success ms-2">Prep Work Orders List</a>
                        <a href="{{ route('prep-orders.create') }}" class="btn btn-sm btn-primary ms-2">Create Prep Work Order</a>
                        <a href="{{ route('daily-input.create') }}" class="btn btn-sm btn-primary ms-2">Create Daily Input</a>
                    </div>
                    

                    <div class="ms-1 header-item d-none d-sm-flex">
                        <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle" data-toggle="fullscreen">
                            <i class='bx bx-fullscreen fs-22'></i>
                        </button>
                    </div>

                   
                    @auth
                        <div class="dropdown ms-sm-3 header-item topbar-user">
                            <button type="button" class="btn" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="d-flex align-items-center">
                                    <img class="rounded-circle header-profile-user" src="{{ asset('assets/images/users/user-dummy-img.jpg') }}" alt="Header Avatar">
                                    <span class="text-start ms-xl-2">
                                        <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">{{ Auth::user()->name}}</span>
                                        <span class="d-none d-xl-block ms-1 fs-12 user-name-sub-text">
                                            @if(Auth::user()->role === 1)
                                                Manager
                                                @elseif(Auth::user()->role === 2)
                                                user
                                            @endif
                                        </span>
                                    </span>
                                </span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <!-- item-->
                    
                                <a class="dropdown-item" onclick="event.preventDefault();document.getElementById('logout-form').submit();"><i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i> <span class="align-middle" data-key="t-logout">Logout</span></a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </header>