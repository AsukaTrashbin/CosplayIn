@extends('layouts.app')

@section('title', 'Profil Tenant '.$tenant->store_name)

@section('content')
<div class="ci-container py-5">

    <a href="{{ route('tenants.index') }}" class="link-arrow d-inline-block mb-4">&larr; Kembali ke daftar tenant</a>

    {{-- Header profil tenant --}}
    <div class="tenant-card mb-5">
        <div class="row g-4 align-items-center">
            <div class="col-12 col-md-auto">
                <div class="tenant-card-media" style="width: 120px; height: 120px;">
                    @if ($costumes->isNotEmpty())
                        <img src="{{ $costumes->first()->primaryImageUrl() }}" alt="{{ $tenant->store_name }}">
                    @endif
                </div>
            </div>

            <div class="col">
                <div class="tenant-badge mb-1">{{ $tenant->statusLabel() }}</div>
                <h1 class="catalog-title mb-2">{{ $tenant->store_name }}</h1>
                <p class="text-muted-ci mb-3">{{ $tenant->address }}</p>

                <div class="d-flex flex-wrap gap-4 tenant-card-meta">
                    <span>{{ $tenant->city }}</span>
                    <span>&#9733; {{ $averageRating > 0 ? number_format($averageRating, 1, ',', '.') : 'Belum ada rating' }}</span>
                    <span>{{ $costumes->total() }} kostum</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Koleksi kostum --}}
    <h2 class="section-title mb-4">Kostum dari {{ $tenant->store_name }}</h2>

    <section class="search-card mb-5">
        <form method="GET" action="{{ route('tenants.show', $tenant) }}" class="row g-0 align-items-center">
            <div class="col-12 col-lg-4 pe-lg-4 mb-3 mb-lg-0 search-field">
                <label for="q" class="search-label d-block mb-2">Kata kunci</label>
                <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}"
                       class="form-control" placeholder="Cari kostum / karakter">
            </div>

            <div class="col-12 col-lg-3 px-lg-4 mb-3 mb-lg-0 search-field">
                <label for="ukuran" class="search-label d-block mb-2">Ukuran</label>
                <select id="ukuran" name="ukuran" class="form-select">
                    <option value="">Semua ukuran</option>
                    @foreach (App\Models\Costume::SIZES as $size)
                        <option value="{{ $size }}" @selected(($filters['ukuran'] ?? '') === $size)>{{ $size }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-lg-3 px-lg-4 mb-3 mb-lg-0 search-field">
                <label for="gender" class="search-label d-block mb-2">Gender</label>
                <select id="gender" name="gender" class="form-select">
                    <option value="">Semua gender</option>
                    @foreach (App\Models\Costume::GENDERS as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['gender'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-lg-2 ps-lg-4">
                <button type="submit" class="btn btn-primary rounded-pill w-100">Filter</button>
            </div>
        </form>
    </section>

    @if ($costumes->isEmpty())
        <div class="catalog-empty-state p-5 text-center">
            <p class="mb-0 text-muted-ci">Tenant belum memiliki kostum yang sesuai filter.</p>
        </div>
    @else
        <div class="row g-4">
            @foreach ($costumes as $costume)
                <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                    <a href="{{ route('catalog.show', $costume) }}" class="text-decoration-none">
                        <article class="costume-card catalog-card">
                            <div class="catalog-card-media mb-3" style="border-radius: 0.65rem; overflow: hidden;">
                                <img src="{{ $costume->primaryImageUrl() }}"
                                     alt="Foto kostum {{ $costume->character_name }}"
                                     style="width: 100%; height: 100%; object-fit: cover; display: block;">
                            </div>
                            <div class="px-2 pb-2">
                                <div class="catalog-card-meta mb-1">{{ $costume->category?->name ?? 'Tanpa kategori' }}</div>
                                <div class="catalog-card-title mb-2" style="color: var(--ci-text);">{{ $costume->character_name }}</div>
                                <div class="costume-price">
                                    {{ rupiah($costume->price_per_day) }}
                                    <span class="per-day">/ hari</span>
                                </div>
                            </div>
                        </article>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-center mt-5">
            {{ $costumes->links() }}
        </div>
    @endif

</div>
@endsection