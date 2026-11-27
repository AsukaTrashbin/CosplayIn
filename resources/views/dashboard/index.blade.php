@extends('layouts.dashboard')

@section('title', 'Ringkasan')

@section('page-title', 'Siap jadi siapa?')
@section('page-subtitle', 'Semua rencana cosplay-mu, dalam satu tempat.')

@section('dashboard')
<div class="dashboard-overview-grid">
    <section class="dashboard-card dashboard-active-card">
        <h2 class="dashboard-section-title mb-0">Pesanan aktif</h2>

        @if ($activeOrder)
            @php
                $costume = $activeOrder->costume;
                $hasImage = $costume?->images->isNotEmpty();
            @endphp

            <div class="dashboard-active-order">
                @if ($hasImage)
                    <img src="{{ $costume->primaryImageUrl() }}"
                         alt="Foto kostum {{ $costume->character_name }}"
                         class="dashboard-active-media">
                @else
                    <div class="dashboard-active-media" aria-hidden="true"></div>
                @endif

                <div class="dashboard-active-info">
                    <p class="dashboard-order-id mb-1">#{{ $activeOrder->booking_code }}</p>
                    <h3 class="dashboard-active-name mb-1">{{ $costume?->character_name }}</h3>
                    <p class="dashboard-meta mb-2">
                        {{ $activeOrder->tenant?->store_name }} &middot; {{ $costume?->city }}
                    </p>
                    <p class="dashboard-order-date mb-2">{{ $activeOrder->rentalPeriodLong() }}</p>
                    <p class="dashboard-meta mb-2">
                        Ukuran {{ $activeOrder->size }} &middot; Durasi {{ $activeOrder->duration_days }} hari
                    </p>
                    <p class="dashboard-total mb-0">
                        Total {{ rupiah($activeOrder->total) }}
                    </p>
                </div>
            </div>

            <a href="{{ route('orders.show', $activeOrder) }}" class="dashboard-btn-soft dashboard-btn-wide mt-4">
                Lihat detail pesanan &rarr;
            </a>
        @else
            <p class="dashboard-meta mb-0">Belum ada pesanan aktif.</p>
        @endif
    </section>

    <section class="dashboard-card dashboard-payment-summary">
        <div>
            <h2 class="dashboard-section-title mb-0">Status pembayaran</h2>

            @if ($paymentOrder)
                @php
                    $badgeClass = match ($paymentOrder->status) {
                        \App\Models\Booking::STATUS_WAITING_PAYMENT => 'dashboard-badge-warning',
                        \App\Models\Booking::STATUS_FAILED => 'dashboard-badge-danger',
                        \App\Models\Booking::STATUS_PAID,
                        \App\Models\Booking::STATUS_READY_TO_PROCESS,
                        \App\Models\Booking::STATUS_RENTED,
                        \App\Models\Booking::STATUS_RETURNED => 'dashboard-badge-success',
                        default => 'dashboard-badge-neutral',
                    };
                @endphp

                <span class="dashboard-badge {{ $badgeClass }} mt-3">
                    {{ $paymentOrder->paymentStatusLabel() }}
                </span>

                <p class="dashboard-amount mb-3">{{ rupiah($paymentOrder->total) }}</p>

                <p class="dashboard-meta mb-1">
                    {{ $paymentOrder->costume?->character_name }} &middot; {{ $paymentOrder->duration_days }} hari
                </p>
                <p class="dashboard-payment-note mb-0">
                    Selesaikan pembayaran agar jadwal sewamu dapat dikonfirmasi.
                </p>
            @else
                <p class="dashboard-meta mt-3 mb-0">Tidak ada tagihan saat ini.</p>
            @endif
        </div>

        @if ($paymentOrder)
            <a href="{{ route('payment.show', $paymentOrder) }}" class="dashboard-btn-pink dashboard-btn-wide">
                Bayar sekarang &rarr;
            </a>
        @endif
    </section>
</div>

<div class="mt-4">
    @include('dashboard.partials.history-card', ['history' => $history, 'showLink' => true])
</div>
@endsection
