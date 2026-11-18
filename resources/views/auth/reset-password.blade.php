@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')
<h1>Reset Password</h1>

<form method="POST" action="{{ route('password.store') }}">
    @csrf

    <input type="hidden" name="token" value="{{ $token }}">

    <p>
        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required autocomplete="email">
    </p>

    <p>
        <label for="password">Password baru (minimal 8 karakter)</label>
        <input id="password" name="password" type="password" required autocomplete="new-password">
    </p>

    <p>
        <label for="password_confirmation">Konfirmasi password baru</label>
        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
    </p>

    <p><button type="submit">Simpan Password Baru</button></p>
</form>
@endsection
