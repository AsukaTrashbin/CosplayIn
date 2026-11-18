@extends('layouts.app')

@section('title', 'Tambah Kostum')

@section('content')
@include('partials.dashboard-nav')

<h1>Tambah Kostum</h1>

<p><a href="{{ route('tenant.costumes.index') }}">&laquo; Kembali ke daftar kostum</a></p>

<form method="POST" action="{{ route('tenant.costumes.store') }}" enctype="multipart/form-data">
    @csrf

    @include('tenant.kostum._form')

    <p><button type="submit">Simpan Kostum</button></p>
</form>
@endsection
