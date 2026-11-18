@extends('layouts.app')

@section('title', 'Lupa Password')

@section('content')
<h1>Lupa Password</h1>

<p>Masukkan email akun Anda. Kami akan mengirim tautan untuk membuat password baru.</p>

<form method="POST" action="{{ route('password.email') }}">
    @csrf

    <p>
        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email">
    </p>

    <p><button type="submit">Kirim Tautan Reset</button></p>
</form>

<p><a href="{{ route('login') }}">Kembali ke halaman login</a></p>
@endsection
