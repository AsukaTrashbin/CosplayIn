<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'CosplayIn') | CosplayIn</title>
    @stack('head')
</head>
<body>
<header>
    <p><a href="{{ route('home') }}">COSPLAYIN</a></p>
    <nav aria-label="Menu utama">
        <ul>
            <li><a href="{{ route('home') }}">Home</a></li>
            <li><a href="{{ route('catalog.index') }}">Katalog</a></li>
            <li><a href="{{ route('tenants.index') }}">List Tenant</a></li>
            @auth
                <li><a href="{{ route(auth()->user()->dashboardRoute()) }}">Dashboard</a></li>
                <li><a href="{{ route('notifications.index') }}">Notifikasi ({{ auth()->user()->unreadNotifications->count() }})</a></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">Logout</button>
                    </form>
                </li>
            @else
                <li><a href="{{ route('login') }}">Login</a></li>
                <li><a href="{{ route('register') }}">Daftar</a></li>
            @endauth
        </ul>
    </nav>
</header>

<main>
    @if (session('success'))
        <p role="status"><strong>Berhasil:</strong> {{ session('success') }}</p>
    @endif

    @if (session('error'))
        <p role="alert"><strong>Gagal:</strong> {{ session('error') }}</p>
    @endif

    @if (session('status'))
        <p role="status">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <div role="alert">
            <p>Terjadi kesalahan pada input Anda:</p>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>

<footer>
    <p>&copy; {{ date('Y') }} CosplayIn. Semua transaksi disimpan minimal 5 tahun.</p>
    <ul>
        <li><a href="{{ route('pages.terms') }}">Syarat dan Ketentuan</a></li>
        <li><a href="{{ route('pages.privacy') }}">Kebijakan Privasi</a></li>
    </ul>
</footer>
</body>
</html>
