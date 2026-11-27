@extends('layouts.dashboard')

@section('title', 'Pesanan Aktif')

@section('page-title', 'Siap jadi siapa?')
@section('page-subtitle', 'Semua rencana cosplay-mu, dalam satu tempat.')

@section('dashboard')
<section class="mt-4">
    <h2 class="dashboard-section-title mb-4">Pesanan aktif</h2>

    @if ($activeOrders->isEmpty())
        <p class="dashboard-meta mb-0">Belum ada pesanan aktif.</p>
    @else
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
            @foreach ($activeOrders as $booking)
                <div class="col">
                    @include('dashboard.partials.order-card', ['booking' => $booking])
                </div>
            @endforeach
        </div>
    @endif
</section>
@endsection
