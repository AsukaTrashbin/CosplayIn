@extends('layouts.app')

@section('title', 'Transaksi')

@section('content')
@include('partials.dashboard-nav')

<h1>Transaksi Platform</h1>

<section>
    <h2>Ringkasan</h2>
    <dl>
        <dt>Total pembayaran sukses</dt>
        <dd>{{ rupiah($summary['total']) }}</dd>
        <dt>Pesanan menunggu pembayaran</dt>
        <dd>{{ $summary['pending'] }}</dd>
        <dt>Pesanan pembayaran gagal</dt>
        <dd>{{ $summary['failed'] }}</dd>
    </dl>
</section>

<form method="GET" action="{{ route('admin.transactions.index') }}">
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
        <label for="q">Cari kode booking, nama, atau email</label>
        <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}">
    </p>
    <p><button type="submit">Filter</button></p>
</form>

@if ($bookings->isEmpty())
    <p>Tidak ada transaksi.</p>
@else
    <table>
        <caption>Daftar transaksi</caption>
        <thead>
            <tr>
                <th scope="col">Kode</th>
                <th scope="col">Customer</th>
                <th scope="col">Tenant</th>
                <th scope="col">Kostum</th>
                <th scope="col">Total</th>
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
                    <td>{{ $booking->tenant->store_name }}</td>
                    <td>{{ $booking->costume->character_name }}</td>
                    <td>{{ rupiah($booking->total) }}</td>
                    <td>{{ $booking->statusLabel() }}</td>
                    <td>{{ $booking->latestPayment?->statusLabel() ?? 'Belum ada' }}</td>
                    <td>
                        <a href="{{ route('admin.transactions.show', $booking) }}">Detail</a>

                        @if (in_array($booking->status, [App\Models\Booking::STATUS_WAITING_PAYMENT, App\Models\Booking::STATUS_FAILED], true))
                            <form method="POST" action="{{ route('admin.transactions.verify-payment', $booking) }}">
                                @csrf
                                <button type="submit">Verifikasi Pembayaran</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $bookings->links() }}
@endif
@endsection
