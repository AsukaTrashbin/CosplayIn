@extends('layouts.app')

@section('title', 'List Tenant')

@section('content')
<h1>Daftar Tenant CosplayIn</h1>

<section>
    <h2>Filter Tenant</h2>
    <form method="GET" action="{{ route('tenants.index') }}">
        <p>
            <label for="q">Cari tenant</label>
            <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="Nama toko atau kata kunci">
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
            <label for="ukuran">Ukuran tersedia</label>
            <select id="ukuran" name="ukuran">
                <option value="">Semua ukuran</option>
                @foreach (App\Models\Costume::SIZES as $size)
                    <option value="{{ $size }}" @selected(($filters['ukuran'] ?? '') === $size)>{{ $size }}</option>
                @endforeach
            </select>
        </p>
        <p>
            <label for="gender">Gender kostum</label>
            <select id="gender" name="gender">
                <option value="">Semua gender</option>
                @foreach (App\Models\Costume::GENDERS as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['gender'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </p>
        <p><button type="submit">Cari Tenant</button></p>
    </form>
</section>

<section>
    <h2>Hasil Pencarian</h2>
    <p>Jumlah tenant: {{ $tenants->total() }}</p>

    @if ($tenants->isEmpty())
        <p>Tidak ada tenant yang sesuai.</p>
    @else
        <ul>
            @foreach ($tenants as $tenant)
                <li>
                    <article>
                        <h3><a href="{{ route('tenants.show', $tenant) }}">{{ $tenant->store_name }}</a></h3>
                        <p>Kota: {{ $tenant->city }}</p>
                        <p>Alamat: {{ $tenant->address }}</p>
                        <p>Jumlah kostum aktif: {{ $tenant->costumes_count }}</p>
                        @if ($tenant->costumes->isNotEmpty())
                            <p>Contoh kostum:
                                @foreach ($tenant->costumes->take(3) as $costume)
                                    <a href="{{ route('catalog.show', $costume) }}">{{ $costume->character_name }}</a>@if (! $loop->last), @endif
                                @endforeach
                            </p>
                        @endif
                    </article>
                </li>
            @endforeach
        </ul>

        {{ $tenants->links() }}
    @endif
</section>
@endsection
