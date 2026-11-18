@extends('layouts.app')

@section('title', 'Verifikasi Email')

@section('content')
<h1>Verifikasi Email Anda</h1>

<p>Terima kasih telah mendaftar di CosplayIn.</p>
<p>Kami telah mengirim tautan verifikasi ke email Anda. Silakan periksa kotak masuk dan klik tautan tersebut sebelum menggunakan dashboard.</p>

@if (session('status'))
    <p role="status">{{ session('status') }}</p>
@endif

<form method="POST" action="{{ route('verification.send') }}">
    @csrf
    <p><button type="submit">Kirim Ulang Email Verifikasi</button></p>
</form>

<form method="POST" action="{{ route('logout') }}">
    @csrf
    <p><button type="submit">Logout</button></p>
</form>
@endsection
