@extends('layouts.app')

@section('title', 'Home')

@section('content')
<section>
    <h1>{{ config('cosplayin.home_banner.title') }}</h1>
    <p>{{ config('cosplayin.home_banner.subtitle') }}</p>
    <p><a href="{{ route('catalog.index') }}">{{ config('cosplayin.home_banner.cta') }}</a></p>
</section>

<section>
    <h2>Cari Kostum</h2>
    <form method="GET" action="{{ route('home') }}">
        <p>
            <label for="q">Kata kunci</label>
            <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="Nama karakter atau kostum">
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
        <p><button type="submit">Cari Kostum</button></p>
    </form>
</section>

<section>
    <h2>Kategori Kostum</h2>
    <ul>
        @forelse ($categories as $category)
            <li>
                <a href="{{ route('catalog.index', ['kategori' => $category->id]) }}">{{ $category->name }}</a>
                ({{ $category->costumes_count }} kostum)
            </li>
        @empty
            <li>Belum ada kategori.</li>
        @endforelse
    </ul>
</section>

<section>
    <h2>Kostum Populer</h2>
    @if ($popular->isEmpty())
        <p>Belum ada kostum populer.</p>
    @else
        <ul>
            @foreach ($popular as $costume)
                <li>
                    <article>
                        <img src="{{ $costume->primaryImageUrl() }}" alt="Foto kostum {{ $costume->character_name }}" width="300" height="200">
                        <h3><a href="{{ route('catalog.show', $costume) }}">{{ $costume->character_name }}</a></h3>
                        <p>Tenant: <a href="{{ route('tenants.show', $costume->tenant) }}">{{ $costume->tenant->store_name }}</a> - {{ $costume->city }}</p>
                        <p>Harga sewa: {{ rupiah($costume->price_per_day) }} / hari</p>
                        <p>Jumlah booking: {{ $costume->bookings_count }}</p>
                    </article>
                </li>
            @endforeach
        </ul>
    @endif
</section>

<section>
    <h2>Kostum Terbaru</h2>
    @if ($costumes->isEmpty())
        <p>Tidak ada kostum yang sesuai dengan pencarian Anda.</p>
    @else
        <ul>
            @foreach ($costumes as $costume)
                <li>
                    <article>
                        <img src="{{ $costume->primaryImageUrl() }}" alt="Foto kostum {{ $costume->character_name }}" width="300" height="200">
                        <h3><a href="{{ route('catalog.show', $costume) }}">{{ $costume->character_name }}</a></h3>
                        <p>{{ $costume->city }} - {{ $costume->genderLabel() }}</p>
                        <p>{{ rupiah($costume->price_per_day) }} / hari</p>
                    </article>
                </li>
            @endforeach
        </ul>

        {{ $costumes->links() }}
    @endif
</section>

<section>
    <h2>Siap Mulai?</h2>
    <p><a href="{{ route('catalog.index') }}">Lihat seluruh katalog kostum</a></p>
</section>
@endsection
