@extends('layouts.app')

@section('title', 'Katalog Kostum')

@section('content')
<div class="ci-container pb-4">

    {{-- HERO / HEADING KATALOG --}}
    <section class="catalog-hero pt-4 pt-lg-5 pb-3">
        <p class="hero-eyebrow mb-2">KOLEKSI COSPLAYIN</p>
        <h1 class="catalog-title mb-2">Karakter baru, cerita baru.</h1>
        <p class="hero-text mb-0">
            Pilih kostum yang paling kamu, untuk momen yang paling ditunggu.
        </p>
    </section>

    <form id="catalogFilterForm" method="GET" action="{{ route('catalog.index') }}">
        {{-- TOP SEARCH & FILTER BAR --}}
        <section class="search-card catalog-search-bar mt-4">
            <div class="row g-3 g-lg-0 align-items-center">
                <div class="col-12 col-lg-4 pe-lg-4 search-field">
                    <label for="q" class="visually-hidden">Cari karakter atau seri</label>
                    <input id="q"
                           name="q"
                           type="search"
                           value="{{ $filters['q'] ?? '' }}"
                           class="form-control catalog-search-input"
                           placeholder="Cari karakter atau seri...">
                </div>

                {{-- Filter Kota (Dropdown Jabodetabek dengan input manual) --}}
                <div class="col-12 col-sm-4 col-lg-2 px-lg-4 search-field position-relative">
                    <input type="hidden" name="kota" id="kotaValue" value="{{ $filters['kota'] ?? '' }}">
                    <div class="catalog-city-dropdown"
                         data-city-dropdown
                         data-cities='@json($cities->values())'>
                        <button type="button"
                                id="kotaDropdownBtn"
                                class="catalog-inline-trigger w-100 d-flex align-items-center justify-content-between gap-2"
                                aria-expanded="false"
                                aria-controls="kotaDropdownMenu">
                            <span class="text-truncate" data-city-label>
                                Kota: {{ ! empty($filters['kota']) ? $filters['kota'] : 'Semua' }}
                            </span>
                            <svg class="catalog-chevron flex-shrink-0" width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>

                        <div id="kotaDropdownMenu" class="catalog-city-menu d-none" role="listbox" aria-label="Pilih kota Jabodetabek">
                            <div class="p-2 border-bottom">
                                <label for="kotaSearchInput" class="visually-hidden">Ketik nama kota Jabodetabek</label>
                                <input id="kotaSearchInput"
                                       type="text"
                                       class="form-control form-control-sm catalog-city-input"
                                       placeholder="Ketik kota (Jabodetabek)..."
                                       autocomplete="off"
                                       data-city-search>
                            </div>
                            <div class="catalog-city-options py-1" data-city-options>
                                <button type="button"
                                        class="catalog-city-option {{ empty($filters['kota']) ? 'is-selected' : '' }}"
                                        data-city-option="">
                                    Semua kota
                                </button>
                            </div>
                            <p class="catalog-city-hint px-3 py-2 mb-0 small text-muted-ci d-none" data-city-empty>
                                Kota hanya tersedia untuk wilayah JABODETABEK.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Filter Ukuran --}}
                <div class="col-12 col-sm-4 col-lg-2 px-lg-4 search-field">
                    <label for="ukuran" class="visually-hidden">Ukuran</label>
                    <select id="ukuran" name="ukuran" class="form-select catalog-inline-select">
                        <option value="">Ukuran: Semua</option>
                        @foreach (App\Models\Costume::SIZES as $size)
                            <option value="{{ $size }}" @selected(($filters['ukuran'] ?? '') === $size)>
                                Ukuran: {{ $size }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Gender --}}
                <div class="col-12 col-sm-4 col-lg-2 px-lg-4 search-field">
                    <label for="gender" class="visually-hidden">Gender</label>
                    <select id="gender" name="gender" class="form-select catalog-inline-select">
                        <option value="">Gender: Semua</option>
                        @foreach (App\Models\Costume::GENDERS as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['gender'] ?? '') === $value)>
                                Gender: {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-lg-2 ps-lg-4">
                    <button type="submit" class="btn btn-primary rounded-pill w-100">Cari</button>
                </div>
            </div>
        </section>

        {{-- MAIN CATALOG LAYOUT --}}
        <section class="row g-4 mt-4 align-items-start">
            {{-- SIDEBAR FILTER KIRI --}}
            <aside class="col-12 col-lg-3">
                <div class="filter-sidebar">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h2 class="filter-sidebar-title mb-0">Filter kostum</h2>
                        <a href="{{ route('catalog.index') }}" class="filter-reset-link">Reset</a>
                    </div>

                    {{-- Kategori Checkbox --}}
                    <div class="mb-4">
                        <h3 class="filter-group-title mb-3">Kategori</h3>
                        <div class="d-flex flex-column gap-2">
                            @foreach ($catalogCategories as $slug => $label)
                                <label class="filter-checkbox-item d-flex align-items-center gap-2">
                                    <input type="checkbox"
                                           name="kategori[]"
                                           value="{{ $slug }}"
                                           class="form-check-input catalog-checkbox mt-0"
                                           @checked(in_array($slug, $selectedCategories, true))>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <hr class="sidebar-divider my-4">

                    {{-- Rentang Harga (2 section atas & bawah ala Tokopedia) --}}
                    <div class="mb-4">
                        <h3 class="filter-group-title mb-3">Rentang harga / hari</h3>
                        <div class="d-flex flex-column gap-2">
                            <div>
                                <label for="harga_min" class="form-label small text-muted-ci mb-1">Harga Minimum</label>
                                <div class="input-group catalog-price-group">
                                    <span class="input-group-text">Rp</span>
                                    <input id="harga_min"
                                           name="harga_min"
                                           type="number"
                                           min="0"
                                           step="1000"
                                           inputmode="numeric"
                                           value="{{ $filters['harga_min'] ?? '' }}"
                                           class="form-control"
                                           placeholder="Harga Minimum">
                                </div>
                            </div>

                            <div>
                                <label for="harga_max" class="form-label small text-muted-ci mb-1">Harga Maksimum</label>
                                <div class="input-group catalog-price-group">
                                    <span class="input-group-text">Rp</span>
                                    <input id="harga_max"
                                           name="harga_max"
                                           type="number"
                                           min="0"
                                           step="1000"
                                           inputmode="numeric"
                                           value="{{ $filters['harga_max'] ?? '' }}"
                                           class="form-control"
                                           placeholder="Harga Maksimum">
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="sidebar-divider my-4">

                    {{-- Ketersediaan --}}
                    <div class="mb-3">
                        <h3 class="filter-group-title mb-3">Ketersediaan</h3>
                        <label class="filter-checkbox-item d-flex align-items-center gap-2">
                            <input type="checkbox"
                                   name="tersedia"
                                   value="1"
                                   class="form-check-input catalog-checkbox mt-0"
                                   @checked(! empty($filters['tersedia']))>
                            <span>Tersedia sekarang</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary rounded-pill w-100 mt-2">
                        Terapkan filter
                    </button>

                    {{-- Highlight Event / Trending --}}
                    @php($topTrending = $trending->first())
                    <div class="event-highlight-box mt-4">
                        <p class="event-highlight-title mb-1">Lagi ramai di event</p>
                        @if ($topTrending)
                            <a href="{{ route('catalog.show', $topTrending) }}" class="event-highlight-link">
                                {{ $topTrending->character_name }} &rarr;
                            </a>
                        @else
                            <a href="{{ route('catalog.index', ['urutkan' => 'ulasan']) }}" class="event-highlight-link">
                                Lihat kostum populer &rarr;
                            </a>
                        @endif
                    </div>
                </div>
            </aside>

            {{-- GRID KATALOG KANAN --}}
            <div class="col-12 col-lg-9">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
                    <p class="catalog-result-count mb-0">
                        {{ $costumes->total() }} kostum untuk kamu
                    </p>

                    <div class="d-flex align-items-center gap-2">
                        <label for="urutkan" class="catalog-sort-label mb-0">Urutkan:</label>
                        <select id="urutkan"
                                name="urutkan"
                                class="form-select catalog-sort-select"
                                onchange="this.form.submit()">
                            @foreach ($sortOptions as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['urutkan'] ?? 'paling_sesuai') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @if ($costumes->isEmpty())
                    <div class="catalog-empty-state text-center py-5 px-4">
                        <h3 class="h5 fw-bold mb-2">Kostum tidak ditemukan</h3>
                        <p class="text-muted-ci mb-4">
                            Belum ada kostum yang cocok dengan kombinasi filter yang kamu pilih.
                        </p>
                        <a href="{{ route('catalog.index') }}" class="btn btn-primary rounded-pill px-4">
                            Reset semua filter
                        </a>
                    </div>
                @else
                    <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-4">
                        @foreach ($costumes as $costume)
                            @php($avgRating = (float) ($costume->reviews_avg_rating ?? 0))
                            <div class="col">
                                <a href="{{ route('catalog.show', $costume) }}" class="text-decoration-none text-reset d-block h-100">
                                    <article class="costume-card catalog-card d-flex flex-column">
                                        <div class="costume-media catalog-card-media position-relative overflow-hidden">
                                            <img src="{{ $costume->primaryImageUrl() }}"
                                                 alt="Foto kostum {{ $costume->character_name }}"
                                                 class="w-100 h-100 object-fit-cover">
                                            @if (! $costume->hasAvailableStock())
                                                <span class="badge-unavailable position-absolute top-0 start-0 m-2">Stok habis</span>
                                            @endif
                                        </div>

                                        <div class="px-2 pt-3 pb-2 d-flex flex-column flex-grow-1">
                                            <p class="catalog-card-meta mb-1">
                                                {{ $costume->category?->name ?? 'Cosplay' }}
                                            </p>
                                            <h3 class="costume-name catalog-card-title mb-1">
                                                {{ $costume->character_name }}
                                            </h3>
                                            <p class="catalog-card-rating mb-3">
                                                <span class="catalog-star" aria-hidden="true">&#9733;</span>
                                                {{ $avgRating > 0 ? number_format($avgRating, 1, ',', '.') : 'Baru' }}
                                                &middot; {{ $costume->city }}
                                            </p>
                                            <p class="costume-price mt-auto mb-0">
                                                {{ rupiah($costume->price_per_day) }}
                                                <span class="per-day">/ hari</span>
                                            </p>
                                        </div>
                                    </article>
                                </a>
                            </div>
                        @endforeach
                    </div>

                    {{ $costumes->links('pagination.catalog') }}
                @endif
            </div>
        </section>
    </form>

</div>
@endsection
