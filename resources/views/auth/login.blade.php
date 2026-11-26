@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="ci-container py-5 my-lg-4">
    <div class="row align-items-center g-5">

        {{-- Panel kiri: ilustrasi / gambar --}}
        <div class="col-lg-6 d-none d-lg-block">
            <div class="auth-visual">
                <div class="auth-visual-inner">
                    <img src="{{ asset('images/auth-visual.png') }}" alt="Ilustrasi login" class="img-fluid">
                </div>
            </div>
        </div>

        {{-- Form kanan --}}
        <div class="col-lg-5 offset-lg-1">
            <div class="auth-form">

                {{-- Switch Login / Buat akun --}}
                <div class="auth-switch mb-4">
                    <a href="{{ route('login') }}" class="active">Login</a>
                    <a href="{{ route('register') }}">Buat akun</a>
                </div>

                <h1 class="section-title mb-3">Selamat datang kembali</h1>
                <p class="text-muted-ci mb-4">Masuk untuk melanjutkan ke akun Anda</p>

                <form method="POST" action="{{ route('login.store') }}" novalidate>
                    @csrf

                    {{-- Email / Username --}}
                    <div class="mb-3">
                        <label for="login" class="form-label auth-label">Email / Username</label>
                        <input
                            id="login"
                            name="login"
                            type="text"
                            class="form-control auth-input @error('login') is-invalid @enderror"
                            placeholder="Masukkan email atau username"
                            value="{{ old('login') }}"
                            required
                            autofocus
                            autocomplete="username"
                        >
                        @error('login')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="mb-3">
                        <label for="password" class="form-label auth-label">Password</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            class="form-control auth-input @error('password') is-invalid @enderror"
                            placeholder="Masukkan password"
                            required
                            autocomplete="current-password"
                        >
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Ingat saya & lupa password --}}
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check mb-0">
                            <input
                                id="remember"
                                name="remember"
                                type="checkbox"
                                value="1"
                                class="form-check-input catalog-checkbox"
                                @checked(old('remember'))
                            >
                            <label for="remember" class="form-check-label auth-link">Ingat saya</label>
                        </div>
                        <a href="{{ route('password.request') }}" class="auth-link">Lupa password?</a>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 auth-submit">Login</button>
                </form>

                <p class="text-center text-muted-ci mt-4 mb-0">
                    Belum punya akun?
                    <a href="{{ route('register') }}" class="auth-link fw-semibold ms-1">Daftar sekarang</a>
                </p>

                <p class="text-center text-muted-ci small mt-4 mb-0">
                    Dengan login, Anda menyetujui
                    <a href="{{ route('pages.terms') }}" class="auth-link">Syarat dan Ketentuan</a>
                    serta
                    <a href="{{ route('pages.privacy') }}" class="auth-link">Kebijakan Privasi</a>
                    CosplayIn.
                </p>
            </div>
        </div>

    </div>
</div>
@endsection