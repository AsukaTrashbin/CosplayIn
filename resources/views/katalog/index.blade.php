@extends('layouts.app')

@section('title', 'Katalog Kostum')

@section('content')
<h1>Katalog Kostum Cosplay</h1>

<section>
    <h2>Filter Pencarian</h2>
    <form method="GET" action="{{ route('catalog.index') }}">
        <p>
            <label for="q">Kata kunci</label>
            <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="Nama karakter">
        </p>
        <p>
            <label for="kota">Kota</label>
            <select id="kota" name="kota">
                <option value="">Semua kota</option>
                @foreach ($cities as $city)
                    <option value="{{ $city }}" @selected(($filters['kota'] ?? '') === $city)>{{ $city }}</option>
                @endforeach
            </select>
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
        <p>
            <label for="kategori">Kategori</label>
            <select id="kategori" name="kategori">
                <option value="">Semua kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) ($filters['kategori'] ?? '') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </p>
        <p>
            <button type="submit">Terapkan Filter</button>
            <a href="{{ route('catalog.index') }}">Reset filter</a>
        </p>
    </form>
</section>

<section>
    <h2>Trend</h2>
    <ol>
        @forelse ($trending as $item)
            <li><a href="{{ route('catalog.show', $item) }}">{{ $item->character_name }}</a> - {{ $item->bookings_count }} booking</li>
        @empty
            <li>Belum ada data trend.</li>
        @endforelse
    </ol>
</section>

<section>
    <h2>Daftar Kostum</h2>
    <p>Jumlah hasil: {{ $costumes->total() }} kostum</p>

    @if ($costumes->isEmpty())
        <p>Tidak ada kostum yang cocok dengan filter Anda.</p>
    @else
        <ul>
            @foreach ($costumes as $costume)
                <li>
                    <article>
                        <img src="{{ $costume->primaryImageUrl() }}" alt="Foto kostum {{ $costume->character_name }}" width="300" height="200">
                        <h3><a href="{{ route('catalog.show', $costume) }}">{{ $costume->character_name }}</a></h3>
                        <p>Tenant: <a href="{{ route('tenants.show', $costume->tenant) }}">{{ $costume->tenant->store_name }}</a></p>
                        <p>Kota: {{ $costume->city }}</p>
                        <p>Kategori: {{ $costume->category?->name ?? 'Tanpa kategori' }}</p>
                        <p>Gender: {{ $costume->genderLabel() }}</p>
                        <p>Harga: {{ rupiah($costume->price_per_day) }} / hari</p>
                        <p>Ukuran tersedia:
                            @foreach ($costume->sizes as $size)
                                {{ $size->size }} (stok {{ $size->stock }})@if (! $loop->last), @endif
                            @endforeach
                        </p>
                        <p><a href="{{ route('catalog.show', $costume) }}">Lihat detail</a></p>
                    </article>
                </li>
            @endforeach
        </ul>

        {{ $costumes->links() }}
    @endif
</section>
@endsection
