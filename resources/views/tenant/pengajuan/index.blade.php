@extends('layouts.app')

@section('title', 'Pengajuan Sewa')

@section('content')
@include('partials.dashboard-nav')

<h1>Pengajuan Sewa Masuk</h1>

<form method="GET" action="{{ route('tenant.bookings.index') }}">
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
    <p>Tidak ada pengajuan sewa.</p>
@else
    <table>
        <caption>Daftar pengajuan sewa</caption>
        <thead>
            <tr>
                <th scope="col">Kode</th>
                <th scope="col">Customer</th>
                <th scope="col">Kostum / ukuran</th>
                <th scope="col">Tanggal sewa</th>
                <th scope="col">Total</th>
                <th scope="col">Status</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($bookings as $booking)
                <tr>
                    <td>{{ $booking->booking_code }}</td>
                    <td>{{ $booking->customer->name }}<br><small>{{ $booking->customer->email }}</small></td>
                    <td>{{ $booking->costume->character_name }} / {{ $booking->size }}</td>
                    <td>{{ tanggal_indonesia($booking->rental_date) }} - {{ tanggal_indonesia($booking->return_date) }} ({{ $booking->duration_days }} hari)</td>
                    <td>{{ rupiah($booking->total) }}</td>
                    <td>{{ $booking->statusLabel() }}</td>
                    <td>
                        <a href="{{ route('tenant.orders.show', $booking) }}">Detail</a>

                        @if ($booking->status === App\Models\Booking::STATUS_WAITING_CONFIRMATION)
                            <form method="POST" action="{{ route('tenant.bookings.approve', $booking) }}">
                                @csrf
                                <button type="submit">Setujui</button>
                            </form>

                            <form method="POST" action="{{ route('tenant.bookings.reject', $booking) }}">
                                @csrf
                                <p>
                                    <label for="alasan_penolakan_{{ $booking->id }}">Alasan penolakan</label>
                                    <input id="alasan_penolakan_{{ $booking->id }}" name="alasan_penolakan" type="text" required>
                                </p>
                                <button type="submit">Tolak</button>
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
