@extends('layouts.app')

@section('title', 'Dashboard Customer')

@section('content')
@include('partials.dashboard-nav')

<h1>Dashboard Customer</h1>
<p>Selamat datang, {{ auth()->user()->name }}.</p>

<section>
    <h2>Ringkasan</h2>
    <dl>
        <dt>Pesanan aktif</dt>
        <dd>{{ $stats['active'] }}</dd>
        <dt>Menunggu pembayaran</dt>
        <dd>{{ $stats['waiting_payment'] }}</dd>
        <dt>Pesanan selesai</dt>
        <dd>{{ $stats['completed'] }}</dd>
        <dt>Total pengeluaran (selesai)</dt>
        <dd>{{ rupiah($stats['total_spent']) }}</dd>
    </dl>
</section>

<section id="pesanan-aktif">
    <h2>Pesanan Aktif</h2>

    @if ($activeOrders->isEmpty())
        <p>Tidak ada pesanan aktif. <a href="{{ route('catalog.index') }}">Mulai menyewa kostum</a>.</p>
    @else
        <table>
            <caption>Daftar pesanan aktif</caption>
            <thead>
                <tr>
                    <th scope="col">Kode</th>
                    <th scope="col">Kostum</th>
                    <th scope="col">Tanggal sewa</th>
                    <th scope="col">Status</th>
                    <th scope="col">Total</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($activeOrders as $booking)
                    <tr>
                        <td>{{ $booking->booking_code }}</td>
                        <td>{{ $booking->costume->character_name }} ({{ $booking->size }})</td>
                        <td>{{ tanggal_indonesia($booking->rental_date) }} - {{ tanggal_indonesia($booking->return_date) }}</td>
                        <td>{{ $booking->statusLabel() }}</td>
                        <td>{{ rupiah($booking->total) }}</td>
                        <td><a href="{{ route('orders.show', $booking) }}">Detail</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</section>

<section id="status-pembayaran">
    <h2>Status Pembayaran</h2>

    @if ($paymentStatus->isEmpty())
        <p>Tidak ada pembayaran yang perlu ditindaklanjuti.</p>
    @else
        <table>
            <caption>Status pembayaran pesanan</caption>
            <thead>
                <tr>
                    <th scope="col">Kode</th>
                    <th scope="col">Status pesanan</th>
                    <th scope="col">Pembayaran terakhir</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($paymentStatus as $booking)
                    <tr>
                        <td>{{ $booking->booking_code }}</td>
                        <td>{{ $booking->statusLabel() }}</td>
                        <td>
                            @if ($booking->latestPayment)
                                {{ $booking->latestPayment->methodLabel() }} - {{ $booking->latestPayment->statusLabel() }}
                            @else
                                Belum ada transaksi
                            @endif
                        </td>
                        <td>
                            @if (in_array($booking->status, [App\Models\Booking::STATUS_WAITING_PAYMENT, App\Models\Booking::STATUS_FAILED], true))
                                <a href="{{ route('payment.show', $booking) }}">Bayar</a>
                            @else
                                <a href="{{ route('orders.show', $booking) }}">Detail</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</section>

<section id="riwayat">
    <h2>Riwayat Sewa</h2>

    @if ($history->isEmpty())
        <p>Belum ada riwayat sewa.</p>
    @else
        <table>
            <caption>Riwayat transaksi selesai atau ditolak</caption>
            <thead>
                <tr>
                    <th scope="col">Kode</th>
                    <th scope="col">Kostum</th>
                    <th scope="col">Status</th>
                    <th scope="col">Total</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($history as $booking)
                    <tr>
                        <td>{{ $booking->booking_code }}</td>
                        <td>{{ $booking->costume->character_name }}</td>
                        <td>{{ $booking->statusLabel() }}</td>
                        <td>{{ rupiah($booking->total) }}</td>
                        <td><a href="{{ route('orders.show', $booking) }}">Detail dan ulasan</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</section>
@endsection
