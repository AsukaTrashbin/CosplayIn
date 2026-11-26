@extends('layouts.app')

@section('title', 'List Tenant')

@section('content')
<div class="ci-container py-5">

    {{-- Header --}}
    <div class="mb-4">
        <div class="hero-eyebrow mb-2">Temukan partner cosplay-mu</div>
        <h1 class="catalog-title mb-2">Tenant pilihan, tampil tanpa khawatir.</h1>
        <p class="text-muted-ci mb-0">Jelajahi penyedia kostum terverifikasi di kota kamu.</p>
    </div>

 {{-- PENCARIAN --}}
<section class="search-card mb-5">
    <form method="GET" action="{{ route('tenants.index') }}" class="row g-0 align-items-center">
        <div class="col-12 col-lg-4 pe-lg-4 mb-3 mb-lg-0 search-field">
            <label for="q" class="search-label d-block mb-2">Nama toko atau kata kunci</label>
            <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}"
                   class="form-control" placeholder="Cari nama toko / kata kunci">
        </div>

        <div class="col-12 col-lg-2 px-lg-4 mb-3 mb-lg-0 search-field">
            <label for="kota" class="search-label d-block mb-2">Kota</label>
            <select id="kota" name="kota" class="form-select">
                <option value="">Semua kota</option>
                @foreach ($cities as $city)
                    <option value="{{ $city }}" @selected(($filters['kota'] ?? '') === $city)>{{ $city }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-12 col-lg-2 px-lg-4 mb-3 mb-lg-0 search-field">
            <label for="ukuran" class="search-label d-block mb-2">Ukuran</label>
            <select id="ukuran" name="ukuran" class="form-select">
                <option value="">Semua ukuran</option>
                @foreach (App\Models\Costume::SIZES as $size)
                    <option value="{{ $size }}" @selected(($filters['ukuran'] ?? '') === $size)>{{ $size }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-12 col-lg-2 px-lg-4 mb-3 mb-lg-0 search-field">
            <label for="gender" class="search-label d-block mb-2">Gender</label>
            <select id="gender" name="gender" class="form-select">
                <option value="">Semua gender</option>
                @foreach (App\Models\Costume::GENDERS as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['gender'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-12 col-lg-2 ps-lg-4">
            <button type="submit" class="btn btn-primary rounded-pill w-100">Cari tenant</button>
        </div>
    </form>
</section>

    {{-- Hasil --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h2 class="section-title mb-0">Pilihan tenant</h2>
        <span class="tenant-count">{{ $tenants->total() }} tenant ditemukan</span>
    </div>

    @if ($tenants->isEmpty())
        <div class="catalog-empty-state p-5 text-center">
            <p class="mb-0 text-muted-ci">Tidak ada tenant yang sesuai dengan pencarianmu.</p>
        </div>
    @else
        <div class="row g-4">
            @foreach ($tenants as $tenant)
                <div class="col-12 col-md-6 col-xl-4">
                    <article class="tenant-card">

                        <div class="d-flex gap-3">
                            <div class="tenant-card-media">
                                @if ($tenant->costumes->isNotEmpty())
                                    <img src="{{ $tenant->costumes->first()->primaryImageUrl() }}" alt="{{ $tenant->store_name }}">
                                @endif
                            </div>

                            <div class="flex-grow-1">
                                <div class="tenant-badge mb-1">Terverifikasi</div>
                                <a href="{{ route('tenants.show', $tenant) }}" class="tenant-card-title d-block mb-2">
                                    {{ $tenant->store_name }}
                                </a>
                                <div class="tenant-card-meta mb-1">{{ $tenant->city }}</div>
                                <div class="tenant-card-meta">
                                    {{ $tenant->costumes_count }} kostum
                                </div>
                            </div>
                        </div>

                        <div class="tenant-card-footer mt-auto pt-3 mt-4 d-flex justify-content-between align-items-center">
                            <span class="tenant-card-meta">
                                @if ($tenant->costumes->isNotEmpty())
                                    Contoh: {{ $tenant->costumes->take(2)->pluck('character_name')->join(', ') }}
                                @else
                                    Belum ada kostum
                                @endif
                            </span>
                            <a href="{{ route('tenants.show', $tenant) }}" class="tenant-card-link">Lihat koleksi &rarr;</a>
                        </div>

                    </article>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-center mt-5">
            {{ $tenants->links() }}
        </div>
    @endif

</div>
@endsection