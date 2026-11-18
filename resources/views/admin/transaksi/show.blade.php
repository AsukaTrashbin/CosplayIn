@extends('layouts.app')

@section('title', 'Detail Transaksi '.$booking->booking_code)

@section('content')
@include('partials.dashboard-nav')

<h1>Transaksi {{ $booking->booking_code }}</h1>
<p>Status: <strong>{{ $booking->statusLabel() }}</strong></p>

<p><a href="{{ route('admin.transactions.index') }}">&laquo; Kembali ke daftar transaksi</a></p>

<section>
    <h2>Pihak Terkait</h2>
    <table>
        <tbody>
            <tr>
                <th scope="row">Customer</th>
                <td>{{ $booking->customer->name }} ({{ $booking->customer->email }})</td>
            </tr>
            <tr>
                <th scope="row">Tenant</th>
                <td>{{ $booking->tenant->store_name }} ({{ $booking->tenant->city }})</td>
            </tr>
            <tr>
                <th scope="row">Penyewa sesuai KTP</th>
                <td>{{ $booking->booker_name }} - {{ $booking->identity_number }}</td>
            </tr>
            <tr>
                <th scope="row">Foto KTP</th>
                <td><a href="{{ route('admin.ktp.show', $booking) }}">Lihat KTP (akses terbatas admin)</a></td>
            </tr>
            <tr>
                <th scope="row">Telepon</th>
                <td>{{ $booking->phone }}</td>
            </tr>
            <tr>
                <th scope="row">Alamat</th>
                <td>{{ $booking->address }}</td>
            </tr>
        </tbody>
    </table>
</section>

<section>
    <h2>Detail Kostum dan Biaya</h2>
    <p>Kostum: {{ $booking->costume->character_name }} ({{ $booking->size }})</p>
    <p>Tanggal: {{ tanggal_indonesia($booking->rental_date) }} - {{ tanggal_indonesia($booking->return_date) }} ({{ $booking->duration_days }} hari)</p>
    <p>Pengiriman: {{ $booking->shipping_method === 'courier' ? 'Kurir' : 'COD' }}</p>

    <table>
        <tbody>
            <tr>
                <th scope="row">Biaya sewa</th>
                <td>{{ rupiah($booking->rental_price) }}</td>
            </tr>
            <tr>
                <th scope="row">Deposit</th>
                <td>{{ rupiah($booking->deposit) }}</td>
            </tr>
            <tr>
                <th scope="row">Biaya pengiriman</th>
                <td>{{ rupiah($booking->shipping_fee) }}</td>
            </tr>
            <tr>
                <th scope="row">Total</th>
                <td><strong>{{ rupiah($booking->total) }}</strong></td>
            </tr>
        </tbody>
    </table>
</section>

<section>
    <h2>Pembayaran</h2>
    @if ($booking->payments->isEmpty())
        <p>Belum ada pembayaran.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th scope="col">ID transaksi</th>
                    <th scope="col">Metode</th>
                    <th scope="col">Nominal</th>
                    <th scope="col">Status</th>
                    <th scope="col">Waktu bayar</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($booking->payments as $payment)
                    <tr>
                        <td>{{ $payment->external_id ?? '-' }}</td>
                        <td>{{ $payment->methodLabel() }}</td>
                        <td>{{ rupiah($payment->amount) }}</td>
                        <td>{{ $payment->statusLabel() }}</td>
                        <td>{{ $payment->paid_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if (in_array($booking->status, [App\Models\Booking::STATUS_WAITING_PAYMENT, App\Models\Booking::STATUS_FAILED], true))
        <form method="POST" action="{{ route('admin.transactions.verify-payment', $booking) }}">
            @csrf
            <p>
                <label for="catatan">Catatan verifikasi manual</label>
                <textarea id="catatan" name="catatan" rows="3">{{ old('catatan') }}</textarea>
            </p>
            <p><button type="submit">Verifikasi Pembayaran Secara Manual</button></p>
        </form>
    @endif
</section>

@if ($booking->returnItem)
    <section>
        <h2>Pengembalian</h2>
        <table>
            <tbody>
                <tr>
                    <th scope="row">Metode</th>
                    <td>{{ $booking->returnItem->methodLabel() }}</td>
                </tr>
                <tr>
                    <th scope="row">Tanggal aktual</th>
                    <td>{{ $booking->returnItem->actual_return_date ? tanggal_indonesia($booking->returnItem->actual_return_date) : '-' }}</td>
                </tr>
                <tr>
                    <th scope="row">Kondisi</th>
                    <td>{{ $booking->returnItem->condition ? $booking->returnItem->conditionLabel() : 'Menunggu verifikasi' }}</td>
                </tr>
                <tr>
                    <th scope="row">Hari terlambat</th>
                    <td>{{ $booking->returnItem->days_late }}</td>
                </tr>
                <tr>
                    <th scope="row">Denda</th>
                    <td>{{ rupiah($booking->returnItem->late_fee) }}</td>
                </tr>
                <tr>
                    <th scope="row">Klaim kerusakan</th>
                    <td>{{ rupiah($booking->returnItem->damage_claim) }}</td>
                </tr>
                <tr>
                    <th scope="row">Deposit dipotong</th>
                    <td>{{ rupiah($booking->returnItem->deposit_deduction) }}</td>
                </tr>
                <tr>
                    <th scope="row">Deposit dikembalikan</th>
                    <td>{{ rupiah($booking->returnItem->deposit_refunded) }}</td>
                </tr>
            </tbody>
        </table>
    </section>
@endif

@if ($booking->reviews->isNotEmpty())
    <section>
        <h2>Ulasan</h2>
        <ul>
            @foreach ($booking->reviews as $review)
                <li>
                    <p><strong>{{ $review->reviewer?->name }}</strong> - {{ $review->target === 'tenant' ? 'untuk tenant' : 'untuk customer' }}</p>
                    <p>Rating {{ $review->rating }}/5</p>
                    <p>{{ $review->comment ?: 'Tanpa komentar.' }}</p>
                </li>
            @endforeach
        </ul>
    </section>
@endif
@endsection
