@extends('layouts.app')

@section('title', 'Edit Kategori')

@section('content')
@include('partials.dashboard-nav')

<h1>Edit Kategori</h1>

<p><a href="{{ route('tenant.categories.index') }}">&laquo; Kembali</a></p>

<form method="POST" action="{{ route('tenant.categories.update', $category) }}">
    @csrf
    @method('PUT')

    <p>
        <label for="name">Nama kategori</label>
        <input id="name" name="name" type="text" value="{{ old('name', $category->name) }}" required>
    </p>

    <p><button type="submit">Perbarui Kategori</button></p>
</form>
@endsection
