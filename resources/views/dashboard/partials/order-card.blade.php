@php
    $costume = $booking->costume;
    $hasImage = $costume?->images->isNotEmpty();
@endphp

<article class="dashboard-order-card">
    <div class="position-relative">
        @if ($hasImage)
            <img src="{{ $costume->primaryImageUrl() }}"
                 alt="Foto kostum {{ $costume->character_name }}"
                 class="dashboard-order-media">
        @else
            <div class="dashboard-order-media" aria-hidden="true"></div>
        @endif

        <span class="dashboard-badge dashboard-badge-active position-absolute top-0 start-0 m-2">Aktif</span>
    </div>

    <div class="dashboard-order-body">
        <p class="dashboard-order-id mb-1">#{{ $booking->booking_code }}</p>
        <h2 class="dashboard-order-name mb-1">{{ $costume?->character_name }}</h2>
        <p class="dashboard-meta mb-1">
            {{ $booking->tenant?->store_name }} &middot; {{ $costume?->city }}
        </p>
        <p class="dashboard-order-date mb-1">{{ $booking->rentalPeriodLong() }}</p>
        <p class="dashboard-meta mb-0">
            Ukuran {{ $booking->size }} &middot; Durasi {{ $booking->duration_days }} hari
        </p>

        <div class="dashboard-order-total">
            <span>Total</span>
            <span class="fw-semibold">{{ rupiah($booking->total) }}</span>
        </div>

        <a href="{{ route('orders.show', $booking) }}" class="dashboard-btn-soft w-100">
            Lihat detail pesanan &rarr;
        </a>
    </div>
</article>
