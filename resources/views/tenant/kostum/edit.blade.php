@extends('layouts.app')

@section('title', 'Edit Kostum')

@section('content')
@include('partials.dashboard-nav')

<h1>Edit Kostum: {{ $costume->character_name }}</h1>

<p><a href="{{ route('tenant.costumes.index') }}">&laquo; Kembali ke daftar kostum</a></p>

<form method="POST" action="{{ route('tenant.costumes.update', $costume) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    @include('tenant.kostum._form')

    <p><button type="submit">Perbarui Kostum</button></p>
</form>

@if ($costume->images->isNotEmpty())
    <section>
        <h2>Foto Saat Ini</h2>
        <p>Centang foto yang ingin dihapus pada formulir di atas, lalu simpan perubahan.</p>
        <ul>
            @foreach ($costume->images as $image)
                <li><img src="{{ $image->url() }}" alt="Foto kostum {{ $costume->character_name }}" width="200" height="150"></li>
            @endforeach
        </ul>
    </section>
@endif
@endsection
