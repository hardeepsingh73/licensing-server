<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            @auth
                <!-- Sidebar toggle button -->
                <button id="sidebarToggle" class="navbar-toggler d-lg-none me-2" type="button" aria-label="Toggle sidebar">
                    <span class="navbar-toggler-icon"></span>
                </button>
            @endauth
            <div class="hs_brand">
                <!-- Brand/logo -->
                <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}" aria-label="Home">
                    <i class="bi bi-rocket-takeoff me-2"></i>
                    <span class="fw-bold">{{ setting('site_name', config('app.name')) }}</span>
                </a>
            </div>
            <!-- Main navbar toggler -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbarContent"
                aria-controls="mainNavbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- Navbar content -->
            <div class="collapse navbar-collapse" id="mainNavbarContent">
                <!-- Left side navigation -->
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="{{ url('/') }}">
                            <i class="bi bi-house-door d-lg-none me-2"></i>Home
                        </a>
                    </li>
                    @can('view dashboard')
                        <li class="nav-item">
                            <a href="{{ route('dashboard') }}"
                                class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <i class="bi bi-speedometer2 me-1"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                    @endcan
                </ul>

                <!-- Right side navigation -->
                <ul class="navbar-nav ms-auto">
                    @if (session()->has('shadow_admin_id'))
                        <li class="nav-item">
                            <a href="{{ route('shadow.logout') }}"
                                class="nav-link text-danger d-flex align-items-center" title="Return to Super Admin">
                                <i class="bi bi-shield-fill-check me-1"></i> Return to Super Admin
                            </a>
                        </li>
                    @endif
                    @guest
                        @if (Route::has('login'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('login') ? 'active' : '' }}"
                                    href="{{ route('login') }}">
                                    <i class="bi bi-box-arrow-in-right d-lg-none me-2"></i>
                                    <span>{{ __('Login') }}</span>
                                </a>
                            </li>
                        @endif

                        @if (Route::has('register'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('register') ? 'active' : '' }}"
                                    href="{{ route('register') }}">
                                    <i class="bi bi-person-plus d-lg-none me-2"></i>
                                    <span>{{ __('Register') }}</span>
                                </a>
                            </li>
                        @endif
                    @else
                        <!-- User dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown"
                                role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="me-2">{{ Auth::user()->name }}</span>
                                <i class="bi bi-person-circle"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('profile') }}">
                                        <i class="bi bi-person me-2"></i>
                                        {{ __('Profile') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="#">
                                        <i class="bi bi-gear me-2"></i>
                                        {{ __('Settings') }}
                                    </a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('logout') }}"
                                        onclick="event.preventDefault(); confirmLogout();">
                                        <i class="bi bi-box-arrow-right me-2"></i>
                                        {{ __('Logout') }}
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>
</header>

@auth
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>
@endauth

@push('scripts')
    <script>
        // Show login success message if redirected from login
        @if (session('login_success'))
            Swal.fire({
                icon: 'success',
                title: 'Login Successful',
                text: 'Welcome back, {{ Auth::user()->name }}!',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });
        @endif

        // Logout confirmation
        function confirmLogout() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You'll need to log in again to access your account.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, log out',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#logout-form').submit();
                }
            });
        }
    </script>
@endpush
