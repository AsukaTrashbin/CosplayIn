@extends('layouts.app')

@section('title', 'Login')

@section('content')
<h1>Login ke CosplayIn</h1>

<p>Belum punya akun? <a href="{{ route('register') }}">Daftar di sini</a>.</p>

<form method="POST" action="{{ route('login.store') }}">
    @csrf

    <p>
        <label for="login">Email atau username</label>
        <input id="login" name="login" type="text" value="{{ old('login') }}" required autofocus autocomplete="username">
    </p>

    <p>
        <label for="password">Password</label>
        <input id="password" name="password" type="password" required autocomplete="current-password">
    </p>

    <p>
        <input id="remember" name="remember" type="checkbox" value="1" @checked(old('remember'))>
        <label for="remember">Ingat saya</label>
    </p>

    <p><button type="submit">Login</button></p>

    <p><a href="{{ route('password.request') }}">Lupa password?</a></p>
</form>

<p>Dengan login, Anda menyetujui <a href="{{ route('pages.terms') }}">Syarat dan Ketentuan</a> serta <a href="{{ route('pages.privacy') }}">Kebijakan Privasi</a> CosplayIn.</p>
@endsection
