@extends('layouts.app')

@section('title', $costume->character_name)

@section('content')
<div class="ci-container py-4 pb-5">

    {{-- Breadcrumb --}}
    <nav class="detail-breadcrumb mb-4" aria-label="breadcrumb">
        <a href="{{ route('home') }}">Home</a> /
        <a href="{{ route('catalog.index') }}">Katalog</a> /
        <span>{{ $costume->character_name }}</span>
    </nav>

    <div class="row g-5">

        {{-- Galeri --}}
        <div class="col-lg-6">
            <div class="detail-main-media mb-3">
                <img src="{{ $costume->primaryImageUrl() }}" alt="Foto utama kostum {{ $costume->character_name }}">
            </div>

            @if ($costume->images->count() > 1)
                <div class="d-flex gap-3 overflow-auto">
                    @foreach ($costume->images as $image)
                        <div class="detail-thumb">
                            <img src="{{ $image->url() }}" alt="Foto tambahan kostum {{ $costume->character_name }}">
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Info --}}
        <div class="col-lg-6">

            <div class="detail-eyebrow mb-2">{{ $costume->category?->name ?? 'Tanpa kategori' }}</div>
            <h1 class="detail-title mb-3">{{ $costume->character_name }}</h1>

            <div class="text-muted-ci mb-4">
                &#9733; {{ $averageRating > 0 ? number_format($averageRating, 1, ',', '.') : 'Belum ada rating' }}
                ({{ $reviews->total() }} ulasan)
                &nbsp;&middot;&nbsp; {{ $costume->city }}
            </div>

            <div class="detail-status mb-4">
                {{ $costume->is_available ? 'Tersedia untuk disewa' : 'Tidak tersedia' }}
            </div>

            <div class="d-flex flex-wrap align-items-baseline gap-2 mb-4">
                <span class="detail-price">{{ rupiah($costume->price_per_day) }}</span>
                <span class="detail-price-unit">/ hari</span>
                <span class="detail-deposit">
                    &middot; Deposit {{ rupiah($costume->deposit > 0 ? $costume->deposit : config('cosplayin.deposit')) }} (dikembalikan)
                </span>
            </div>

            <p class="text-muted-ci mb-4" style="max-width: 36rem;">{{ $costume->description }}</p>

            <hr class="sidebar-divider my-4">

            {{-- Kelengkapan --}}
            <div class="mb-4">
                <div class="detail-section-title mb-2">Kelengkapan kostum</div>
                <p class="mb-0" style="white-space: pre-line;">{{ $costume->accessories ?: 'Informasi aksesori belum diisi tenant.' }}</p>
            </div>

            {{-- Ukuran --}}
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="detail-section-title">Pilih ukuran</div>
                    <span class="detail-small-note">Panduan ukuran</span>
                </div>

                <div class="d-flex flex-wrap gap-3">
                    @foreach (App\Models\Costume::SIZES as $size)
                        @php($stock = $costume->sizes->firstWhere('size', $size)?->stock ?? 0)
                        <button type="button"
                                class="detail-size-btn {{ $loop->index === 1 ? 'active' : '' }}"
                                @disabled($stock < 1)>
                            {{ $size }}
                        </button>
                    @endforeach
                </div>
            </div>

            <form method="GET" action="{{ route('catalog.availability', $costume) }}" class="mb-4">
                <details>
                    <summary class="fw-semibold" style="cursor: pointer;">Cek ketersediaan tanggal &rarr;</summary>
                    <div class="row g-3 mt-1">
                        <div class="col-md-4">
                            <label for="cek_ukuran" class="form-label auth-label">Ukuran</label>
                            <select id="cek_ukuran" name="ukuran" class="form-select auth-input" required>
                                @foreach (App\Models\Costume::SIZES as $size)
                                    <option value="{{ $size }}">{{ $size }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="cek_tanggal_mulai" class="form-label auth-label">Tanggal mulai</label>
                            <input id="cek_tanggal_mulai" name="tanggal_mulai" type="date"
                                   class="form-control auth-input" min="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="cek_tanggal_selesai" class="form-label auth-label">Tanggal kembali</label>
                            <input id="cek_tanggal_selesai" name="tanggal_selesai" type="date"
                                   class="form-control auth-input" min="{{ now()->addDay()->toDateString() }}" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary rounded-pill mt-3 px-4">Cek Ketersediaan</button>
                </details>
            </form>

            @if ($availability)
                <p role="status" class="mb-4">
                    Ukuran {{ $availability['ukuran'] }} untuk {{ $availability['tanggal_mulai'] }} sampai {{ $availability['tanggal_selesai'] }}:
                    @if ($availability['available'] > 0)
                        tersedia ({{ $availability['available_stock'] }} unit).
                    @else
                        tidak tersedia.
                    @endif
                </p>
            @endif

            {{-- CTA sewa --}}
            <a href="{{ route('booking.create', $costume) }}"
               class="btn btn-primary w-100 auth-submit d-block text-center mb-2">
                Sewa sekarang &rarr;
            </a>
            <p class="detail-small-note mb-0">Dibersihkan setiap selesai sewa &bull; Foto sesuai produk</p>

            @guest
                <p class="detail-small-note mt-2 mb-0">Anda akan diarahkan ke halaman login terlebih dahulu.</p>
            @endguest
        </div>
    </div>

    {{-- Tenant --}}
    <div class="detail-tenant-card d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-5">
        <div>
            <div class="fw-semibold mb-1">Disewakan oleh {{ $costume->tenant->store_name }}</div>
            <div class="text-muted-ci small">Tenant terverifikasi &middot; {{ $costume->tenant->city }}</div>
        </div>
        <a href="{{ route('tenants.show', $costume->tenant) }}"
           class="btn btn-primary rounded-pill px-4">Lihat tenant &rarr;</a>
    </div>

</div>
@endsection