@extends('layouts.app')

@section('title', 'Home')

@section('content')
<div class="ci-container pb-2">

    {{-- HERO --}}
    <section class="hero">
        <div class="d-flex flex-column flex-lg-row align-items-stretch">
            <div class="hero-copy d-flex flex-column justify-content-center">
                <p class="hero-eyebrow mb-4">Rent the look. Own the moment.</p>

                <h1 class="hero-title mb-4">
                    Jadi karakter favoritmu.<br>
                    Tanpa harus beli.
                </h1>

                <p class="hero-text mb-5">
                    Dari anime hingga game, temukan kostum impianmu dari tenant pilihan.
                    Sewa mudah, siap tampil di event berikutnya.
                </p>

                <div>
                    <a href="{{ route('catalog.index') }}" class="btn btn-primary rounded-pill px-4 py-3">
                        Mulai menyewa &rarr;
                    </a>
                </div>
            </div>

            <div class="hero-media flex-grow-1 d-none d-lg-block" aria-hidden="true"></div>
        </div>
    </section>

    {{-- PENCARIAN --}}
    <section class="search-card mt-5">
        <form method="GET" action="{{ route('catalog.index') }}" class="row g-0 align-items-center">
            <div class="col-12 col-lg-4 pe-lg-4 mb-3 mb-lg-0 search-field">
                <label for="q" class="search-label d-block mb-2">Karakter atau seri</label>
                <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}"
                       class="form-control" placeholder="Cari kostum / karakter">
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
                <button type="submit" class="btn btn-primary rounded-pill w-100">Cari kostum</button>
            </div>
        </form>
    </section>

    {{-- KATEGORI --}}
    <section class="mt-5">
        <h2 class="section-title mb-3">Kategori kostum</h2>

        <div class="d-flex flex-wrap gap-3">
            <a href="{{ route('catalog.index') }}" class="btn category-pill active">Semua kostum</a>
            {{-- TODO: arahkan ke filter kategori setelah data kategori tersedia. --}}
            <a href="#" class="btn category-pill">Anime</a>
            <a href="#" class="btn category-pill">Game</a>
            <a href="#" class="btn category-pill">Film &amp; serial</a>
            <a href="#" class="btn category-pill">Original</a>
        </div>
    </section>

    {{-- COSPLAY POPULER --}}
    <section class="mt-5">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <h2 class="section-title mb-0">Cosplay populer</h2>
            <a href="{{ route('catalog.index') }}" class="link-arrow text-end lh-sm">
                Lihat semua<br>&rarr;
            </a>
        </div>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4">
            @foreach ($popularCosplays as $cosplay)
                <div class="col">
                    <article class="costume-card">
                        <div class="position-relative">
                            <div class="costume-media" aria-hidden="true"></div>
                            <span class="badge-available position-absolute top-0 start-0 m-2">Tersedia</span>
                        </div>

                        <div class="px-2 pt-3 pb-2">
                            <h3 class="costume-name mb-2">{{ $cosplay['name'] }}</h3>
                            <p class="costume-price mb-0">
                                Rp{{ number_format($cosplay['price'], 0, ',', '.') }}
                                <span class="per-day">/ hari</span>
                            </p>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    </section>

    {{-- CARA MENYEWA --}}
    <section class="mt-5">
        <h2 class="section-title mb-3">Cara menyewa</h2>

        <div class="how-box p-4 p-lg-5">
            <div class="row g-4 g-lg-5">
                <div class="col-md-4">
                    <p class="step-number mb-2">01</p>
                    <h3 class="step-title mb-2">Pilih kostum</h3>
                    <p class="step-text mb-0">Temukan karakter &amp; ukuranmu.</p>
                </div>

                <div class="col-md-4">
                    <p class="step-number mb-2">02</p>
                    <h3 class="step-title mb-2">Atur tanggal</h3>
                    <p class="step-text mb-0">Sesuaikan dengan jadwal event.</p>
                </div>

                <div class="col-md-4">
                    <p class="step-number mb-2">03</p>
                    <h3 class="step-title mb-2">Siap tampil</h3>
                    <p class="step-text mb-0">Bayar, ambil, dan jadi bintang.</p>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
