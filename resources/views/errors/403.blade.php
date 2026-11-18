@extends('layouts.app')

@section('title', 'Akses Ditolak')

@section('content')
<h1>Akses Ditolak</h1>
<p>Anda tidak memiliki izin untuk mengakses halaman ini.</p>
<p><a href="{{ route('home') }}">Kembali ke halaman utama</a></p>
@endsection
