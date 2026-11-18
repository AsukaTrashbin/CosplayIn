@extends('layouts.app')

@section('title', 'Dashboard Tenant')

@section('content')
@include('partials.dashboard-nav')

<h1>Dashboard Tenant</h1>
<p>Toko: <strong>{{ $tenant->store_name }}</strong> ({{ $tenant->city }})</p>
<p>Status tenant: {{ $tenant->statusLabel() }}</p>

<section>
    <h2>Ringkasan Performa</h2>
    <dl>
        <dt>Total customer yang pernah booking</dt>
        <dd>{{ $totalUsers }}</dd>
        <dt>Total kostum</dt>
        <dd>{{ $totalCostumes }}</dd>
        <dt>Total pendapatan (pembayaran sukses)</dt>
        <dd>{{ rupiah($revenue) }}</dd>
        <dt>Pendapatan bersih setelah komisi {{ $commissionPercent }}%</dt>
        <dd>{{ rupiah($earnings) }}</dd>
        <dt>Rating rata-rata</dt>
        <dd>{{ $averageRating > 0 ? $averageRating.' / 5' : 'Belum ada rating' }}</dd>
        <dt>Pengajuan menunggu konfirmasi</dt>
        <dd>{{ $pendingRequests }}</dd>
    </dl>
</section>

<section>
    <h2>Akses Cepat</h2>
    <ul>
        <li><a href="{{ route('tenant.bookings.index') }}">Lihat pengajuan sewa ({{ $pendingRequests }})</a></li>
        <li><a href="{{ route('tenant.costumes.create') }}">Tambah kostum baru</a></li>
        <li><a href="{{ route('tenant.orders.index') }}">Kelola pesanan aktif</a></li>
        <li><a href="{{ route('tenant.reports') }}">Lihat laporan pendapatan</a></li>
    </ul>
</section>

<section>
    <h2>Pengajuan Terbaru</h2>

    @if ($recentBookings->isEmpty())
        <p>Belum ada pesanan.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th scope="col">Kode</th>
                    <th scope="col">Customer</th>
                    <th scope="col">Kostum</th>
                    <th scope="col">Tanggal sewa</th>
                    <th scope="col">Status</th>
                    <th scope="col">Total</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($recentBookings as $booking)
                    <tr>
                        <td>{{ $booking->booking_code }}</td>
                        <td>{{ $booking->customer->name }}</td>
                        <td>{{ $booking->costume->character_name }}</td>
                        <td>{{ tanggal_indonesia($booking->rental_date) }} - {{ tanggal_indonesia($booking->return_date) }}</td>
                        <td>{{ $booking->statusLabel() }}</td>
                        <td>{{ rupiah($booking->total) }}</td>
                        <td><a href="{{ route('tenant.orders.show', $booking) }}">Detail</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</section>
@endsection
