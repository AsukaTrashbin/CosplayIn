@extends('layouts.app')

@section('title', 'Tambah Kategori')

@section('content')
@include('partials.dashboard-nav')

<h1>Tambah Kategori</h1>

<p><a href="{{ route('tenant.categories.index') }}">&laquo; Kembali</a></p>

<form method="POST" action="{{ route('tenant.categories.store') }}">
    @csrf

    <p>
        <label for="name">Nama kategori</label>
        <input id="name" name="name" type="text" value="{{ old('name') }}" required>
    </p>

    <p><button type="submit">Simpan Kategori</button></p>
</form>
@endsection
