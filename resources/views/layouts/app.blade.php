<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'CosplayIn') | CosplayIn</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">

<header>
    <nav class="navbar navbar-expand-lg navbar-ci" aria-label="Menu utama">
        <div class="ci-container d-flex flex-wrap align-items-center justify-content-between">
            <a class="ci-brand" href="{{ route('home') }}">cosplayin.</a>

            <button class="navbar-toggler border-0 px-2" type="button"
                    data-bs-toggle="collapse" data-bs-target="#menuUtama"
                    aria-controls="menuUtama" aria-expanded="false" aria-label="Buka menu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="menuUtama">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2 mt-3 mt-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}"
                           href="{{ route('home') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('catalog.*') ? 'active' : '' }}"
                           href="{{ route('catalog.index') }}">Katalog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('tenants.*') ? 'active' : '' }}"
                           href="{{ route('tenants.index') }}">Tenant</a>
                    </li>

                    @auth
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}"
                               href="{{ route('notifications.index') }}">
                                Notifikasi
                                @if (auth()->user()->unreadNotifications->count() > 0)
                                    <span class="badge rounded-pill text-bg-light">{{ auth()->user()->unreadNotifications->count() }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route(auth()->user()->dashboardRoute()) }}">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="nav-link border-0 bg-transparent">Logout</button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('login') ? 'active' : '' }}"
                               href="{{ route('login') }}">Login</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>
</header>

<main class="flex-grow-1">
    @if (session('success'))
        <div class="ci-container pt-4">
            <div class="alert alert-success mb-0" role="status">
                <strong>Berhasil:</strong> {{ session('success') }}
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="ci-container pt-4">
            <div class="alert alert-danger mb-0" role="alert">
                <strong>Gagal:</strong> {{ session('error') }}
            </div>
        </div>
    @endif

    @if (session('status'))
        <div class="ci-container pt-4">
            <div class="alert alert-info mb-0" role="status">{{ session('status') }}</div>
        </div>
    @endif

    @if ($errors->any())
        <div class="ci-container pt-4">
            <div class="alert alert-danger mb-0" role="alert">
                <p class="mb-2">Terjadi kesalahan pada input Anda:</p>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @yield('content')
</main>

<footer class="mt-5">
    <div class="ci-container">
        <div class="footer-divider py-4">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div class="d-flex flex-column flex-md-row align-items-md-baseline gap-2 gap-md-4">
                    <a class="ci-brand" href="{{ route('home') }}">cosplayin.</a>
                    <span class="text-muted-ci small">Sewa kostum. Rayakan imajinasimu.</span>
                </div>

                <ul class="list-unstyled d-flex flex-wrap gap-4 mb-0 small">
                    {{-- TODO: ganti dengan route halaman bantuan saat tersedia. --}}
                    <li><a class="footer-link" href="#">Bantuan</a></li>
                    <li><a class="footer-link" href="{{ route('pages.terms') }}">Syarat sewa</a></li>
                    {{-- TODO: ganti dengan URL Instagram resmi. --}}
                    <li><a class="footer-link" href="#">Instagram</a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>

@stack('scripts')
</body>
</html>
