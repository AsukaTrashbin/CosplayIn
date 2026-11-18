@extends('layouts.app')

@section('title', 'Profil Tenant '.$tenant->store_name)

@section('content')
<article>
    <p><a href="{{ route('tenants.index') }}">&laquo; Kembali ke daftar tenant</a></p>

    <h1>{{ $tenant->store_name }}</h1>
    <p>Kota: {{ $tenant->city }}</p>
    <p>Alamat: {{ $tenant->address }}</p>
    <p>Rating rata-rata: {{ $averageRating > 0 ? $averageRating.' / 5' : 'Belum ada rating' }}</p>
    <p>Status tenant: {{ $tenant->statusLabel() }}</p>

    <section>
        <h2>Kostum dari {{ $tenant->store_name }}</h2>

        <form method="GET" action="{{ route('tenants.show', $tenant) }}">
            <p>
                <label for="q">Kata kunci</label>
                <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}">
            </p>
            <p>
                <label for="ukuran">Ukuran</label>
                <select id="ukuran" name="ukuran">
                    <option value="">Semua ukuran</option>
                    @foreach (App\Models\Costume::SIZES as $size)
                        <option value="{{ $size }}" @selected(($filters['ukuran'] ?? '') === $size)>{{ $size }}</option>
                    @endforeach
                </select>
            </p>
            <p>
                <label for="gender">Gender</label>
                <select id="gender" name="gender">
                    <option value="">Semua gender</option>
                    @foreach (App\Models\Costume::GENDERS as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['gender'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </p>
            <p><button type="submit">Filter</button></p>
        </form>

        @if ($costumes->isEmpty())
            <p>Tenant belum memiliki kostum yang sesuai filter.</p>
        @else
            <ul>
                @foreach ($costumes as $costume)
                    <li>
                        <article>
                            <img src="{{ $costume->primaryImageUrl() }}" alt="Foto kostum {{ $costume->character_name }}" width="300" height="200">
                            <h3><a href="{{ route('catalog.show', $costume) }}">{{ $costume->character_name }}</a></h3>
                            <p>Kategori: {{ $costume->category?->name ?? 'Tanpa kategori' }}</p>
                            <p>{{ rupiah($costume->price_per_day) }} / hari</p>
                        </article>
                    </li>
                @endforeach
            </ul>

            {{ $costumes->links() }}
        @endif
    </section>
</article>
@endsection
