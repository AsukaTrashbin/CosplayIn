@extends('layouts.dashboard')

@section('title', 'Status Pembayaran')

@section('page-title', 'Status pembayaran')
@section('page-subtitle', 'Semua status pembayaran, dalam satu tempat.')

@section('dashboard')
<section class="dashboard-panel dashboard-panel-lg mt-4">
    <div class="dashboard-panel-header dashboard-payment-header">
        <h2 class="dashboard-payment-title mb-0">Status Pembayaran</h2>

        <div class="dashboard-filter-pills">
            @foreach ($statusFilters as $value => $label)
                <a href="{{ route('customer.payments.index', $value === 'semua' ? [] : ['status' => $value]) }}"
                   class="dashboard-filter-pill {{ $activeFilter === $value ? 'active' : '' }}"
                   @if ($activeFilter === $value) aria-current="page" @endif>
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    <hr class="dashboard-panel-divider">

    @if ($paymentOrders->isEmpty())
        <p class="dashboard-empty mb-0">Tidak ada status pembayaran untuk filter ini.</p>
    @else
        <div class="dashboard-payment-list">
            @foreach ($paymentOrders as $booking)
                @php
                    $pillClass = match ($booking->status) {
                        \App\Models\Booking::STATUS_FAILED => 'is-danger',
                        \App\Models\Booking::STATUS_PAID,
                        \App\Models\Booking::STATUS_READY_TO_PROCESS,
                        \App\Models\Booking::STATUS_RENTED,
                        \App\Models\Booking::STATUS_RETURNED => 'is-success',
                        default => '',
                    };
                @endphp

                <div class="dashboard-payment-row">
                    <span class="dashboard-payment-id">#{{ $booking->booking_code }}</span>
                    <span class="dashboard-payment-character">{{ $booking->costume?->character_name }}</span>
                    <span class="dashboard-payment-date">{{ $booking->rentalPeriodShort() }}</span>
                    <span class="dashboard-status-pill {{ $pillClass }}">{{ $booking->paymentStatusLabel() }}</span>
                    <span class="dashboard-payment-amount">{{ rupiah($booking->total) }}</span>

                    @if ($booking->isPaymentDue())
                        <a href="{{ route('payment.show', $booking) }}" class="dashboard-pay-button">Bayar</a>
                    @else
                        <span class="dashboard-pay-spacer" aria-hidden="true"></span>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</section>
@endsection
