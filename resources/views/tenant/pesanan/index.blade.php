@extends('layouts.app')

@section('title', 'Pesanan Tenant')

@section('content')
@include('partials.dashboard-nav')

<h1>Pesanan Tenant</h1>

<form method="GET" action="{{ route('tenant.orders.index') }}">
    <p>
        <label for="status">Status</label>
        <select id="status" name="status">
            <option value="">Semua status</option>
            @foreach (App\Models\Booking::STATUSES as $status)
                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>
            @endforeach
        </select>
    </p>
    <p>
        <label for="q">Cari kode atau nama penyewa</label>
        <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}">
    </p>
    <p><button type="submit">Filter</button></p>
</form>

@if ($bookings->isEmpty())
    <p>Tidak ada pesanan.</p>
@else
    <table>
        <caption>Daftar pesanan</caption>
        <thead>
            <tr>
                <th scope="col">Kode</th>
                <th scope="col">Customer</th>
                <th scope="col">Kostum</th>
                <th scope="col">Tanggal sewa</th>
                <th scope="col">Status</th>
                <th scope="col">Pembayaran</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($bookings as $booking)
                <tr>
                    <td>{{ $booking->booking_code }}</td>
                    <td>{{ $booking->customer->name }}</td>
                    <td>{{ $booking->costume->character_name }} ({{ $booking->size }})</td>
                    <td>{{ tanggal_indonesia($booking->rental_date) }} - {{ tanggal_indonesia($booking->return_date) }}</td>
                    <td>{{ $booking->statusLabel() }}</td>
                    <td>{{ $booking->latestPayment?->statusLabel() ?? 'Belum ada' }}</td>
                    <td><a href="{{ route('tenant.orders.show', $booking) }}">Detail</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $bookings->links() }}
@endif
@endsection
