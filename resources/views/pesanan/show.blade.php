@extends('layouts.app')

@section('title', 'Pesanan '.$booking->booking_code)

@section('content')
@php
    $isCancelled = $booking->status === \App\Models\Booking::STATUS_REJECTED;

    $currentStep = match ($booking->status) {
        \App\Models\Booking::STATUS_WAITING_CONFIRMATION => 2,
        \App\Models\Booking::STATUS_WAITING_PAYMENT,
        \App\Models\Booking::STATUS_FAILED,
        \App\Models\Booking::STATUS_PAID,
        \App\Models\Booking::STATUS_READY_TO_PROCESS,
        \App\Models\Booking::STATUS_RENTED,
        \App\Models\Booking::STATUS_RETURNED,
        \App\Models\Booking::STATUS_COMPLETED => 3,
        default => 1,
    };

    $imageUrl = $booking->costume?->primaryImageUrl();
    $hasImage = $imageUrl && ! str_contains($imageUrl, 'placeholder.svg');
@endphp

<div class="ci-container py-4 pb-5">

    <a href="{{ route('customer.orders.active') }}" class="link-arrow d-inline-block mb-4">&larr; Kembali ke daftar pesanan</a>

    <div class="detail-eyebrow mb-2">Detail pesanan</div>
    <h1 class="catalog-title mb-3">#{{ $booking->booking_code }}</h1>

    @if ($isCancelled)
        <p class="text-muted-ci mb-4">Pesanan dibatalkan</p>
        @if ($booking->rejection_reason)
            <p class="detail-small-note mb-4">Alasan: {{ $booking->rejection_reason }}</p>
        @endif
    @else
        <div class="booking-steps d-flex flex-wrap align-items-center gap-2 gap-md-0 mb-5">
            <span>1 Pengajuan</span>
            <span class="step-line d-none d-md-inline-block"></span>
            <span class="{{ $currentStep === 2 ? 'step-active' : '' }}">2 Persetujuan tenant</span>
            <span class="step-line d-none d-md-inline-block"></span>
            <span class="{{ $currentStep === 3 ? 'step-active' : '' }}">3 Pembayaran</span>
        </div>
    @endif

    <div class="row g-4 align-items-start">

        {{-- Kiri: detail penyewaan --}}
        <div class="col-lg-7">
            <div class="search-card p-4 p-lg-5">
                <h2 class="section-title fs-3 mb-4">Detail penyewaan</h2>

                <div class="pay-summary-box mb-4">
                    <div class="text-muted-ci small mb-2">{{ $booking->booking_code }}</div>
                    <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
                        <span class="fw-semibold">{{ $booking->statusLabel() }}</span>
                        <span class="pay-total">{{ rupiah($booking->total) }}</span>
                    </div>
                </div>

                <div class="booking-summary-row">
                    <span class="label">Tanggal sewa</span>
                    <span class="value text-end ms-3">{{ tanggal_indonesia($booking->rental_date) }} - {{ tanggal_indonesia($booking->return_date) }}</span>
                </div>
                <div class="booking-summary-row">
                    <span class="label">Durasi</span>
                    <span class="value">{{ $booking->duration_days }} hari</span>
                </div>
                <div class="booking-summary-row">
                    <span class="label">Ukuran</span>
                    <span class="value">{{ $booking->size }}</span>
                </div>
                <div class="booking-summary-row">
                    <span class="label">Metode pengiriman</span>
                    <span class="value">{{ $booking->shipping_method === 'courier' ? 'Kurir / Ekspedisi' : 'COD / Langsung' }}</span>
                </div>
                <div class="booking-summary-row">
                    <span class="label">Nama pemesan</span>
                    <span class="value">{{ $booking->booker_name }}</span>
                </div>
                <div class="booking-summary-row">
                    <span class="label">Nomor telepon</span>
                    <span class="value">{{ $booking->phone }}</span>
                </div>
                <div class="booking-summary-row">
                    <span class="label">Alamat lengkap</span>
                    <span class="value text-end ms-3">{{ $booking->address }}</span>
                </div>

                <div class="border-top mt-4 pt-4">
                    <div class="fw-semibold mb-3">Dokumen</div>

                    <div class="booking-summary-row">
                        <span class="label">KTP</span>
                        <span class="value">
                            @if ($booking->ktp_path)
                                <span class="badge-available">Sudah diunggah</span>
                            @else
                                <span class="badge-unavailable">Belum diunggah</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kanan: ringkasan pesanan --}}
        <div class="col-lg-5">
            <div class="search-card p-4 p-lg-5">
                <h2 class="section-title fs-3 mb-4">Ringkasan pesanan</h2>

                <div class="d-flex gap-3 align-items-start pb-4 mb-3 border-bottom">
                    @if ($hasImage)
                        <img src="{{ $imageUrl }}"
                             alt="Foto kostum {{ $booking->costume->character_name }}"
                             style="width: 88px; height: 112px; flex-shrink: 0; border-radius: .75rem; object-fit: cover; background: var(--ci-placeholder); display: block;">
                    @else
                        <div style="width: 88px; height: 112px; flex-shrink: 0; border-radius: .75rem; background: var(--ci-placeholder);" aria-hidden="true"></div>
                    @endif

                    <div style="min-width: 0;">
                        <div class="fw-semibold fs-5 mb-1">{{ $booking->costume->character_name }}</div>
                        <div class="booking-hint mb-2">
                            {{ $booking->costume->category?->name ?? 'Tanpa kategori' }} &middot; Ukuran {{ $booking->size }}
                        </div>
                        <div class="booking-hint">
                            {{ $booking->costume->tenant?->store_name ?? 'Tenant' }}, {{ $booking->costume->city }}
                        </div>
                    </div>
                </div>

                <div class="booking-summary-row">
                    <span class="label">Harga sewa</span>
                    <span class="value">{{ rupiah($booking->rental_price) }}</span>
                </div>
                <div class="booking-summary-row">
                    <span class="label">Deposit (dikembalikan)</span>
                    <span class="value">{{ rupiah($booking->deposit) }}</span>
                </div>
                <div class="booking-summary-row">
                    <span class="label">Ongkir</span>
                    <span class="value">{{ rupiah($booking->shipping_fee) }}</span>
                </div>

                <hr class="sidebar-divider my-4">

                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Total pembayaran</span>
                    <span class="pay-total">{{ rupiah($booking->total) }}</span>
                </div>

                @if (in_array($booking->status, [\App\Models\Booking::STATUS_WAITING_PAYMENT, \App\Models\Booking::STATUS_FAILED], true))
                    <a href="{{ route('payment.show', $booking) }}" class="btn btn-primary auth-submit w-100 mt-4">
                        Bayar sekarang &rarr;
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Riwayat status --}}
    <div class="search-card p-4 p-lg-5 mt-4">
        <h2 class="section-title fs-4 mb-4">Riwayat status</h2>

        @if (method_exists($booking, 'statusHistory') && $booking->statusHistory->isNotEmpty())
            @foreach ($booking->statusHistory as $history)
                <div class="booking-summary-row">
                    <div>
                        <div class="fw-medium">
                            {{ $history->created_at?->locale('id')->translatedFormat('d M Y, H:i') }}
                        </div>
                        @if (! empty($history->note))
                            <div class="booking-hint">{{ $history->note }}</div>
                        @endif
                    </div>
                    <span class="value">{{ $history->statusLabel ?? $history->status }}</span>
                </div>
            @endforeach
        @else
            <div class="catalog-empty-state p-4 text-center">
                <p class="text-muted-ci mb-0">Belum ada riwayat status.</p>
            </div>
        @endif
    </div>

    <p class="detail-small-note mt-4 mb-0">Butuh bantuan? Hubungi tenant atau pusat bantuan.</p>
</div>
@endsection
