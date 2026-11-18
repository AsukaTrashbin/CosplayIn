@extends('layouts.app')

@section('title', 'Pesanan '.$booking->booking_code)

@if ($booking->isActive())
    @push('head')
        <meta http-equiv="refresh" content="30">
    @endpush
@endif

@section('content')
<h1>Pesanan {{ $booking->booking_code }}</h1>

<p>Halaman ini diperbarui otomatis setiap 30 detik selama pesanan masih aktif. <a href="{{ route('orders.show', $booking) }}">Muat ulang sekarang</a>.</p>

<section>
    <h2>Status Pesanan</h2>
    <p><strong>{{ $booking->statusLabel() }}</strong></p>
    @if ($booking->rejection_reason)
        <p>Alasan penolakan: {{ $booking->rejection_reason }}</p>
    @endif
</section>

<section>
    <h2>Detail Kostum</h2>
    <img src="{{ $booking->costume->primaryImageUrl() }}" alt="Foto kostum {{ $booking->costume->character_name }}" width="300" height="200">
    <p>Kostum: <a href="{{ route('catalog.show', $booking->costume) }}">{{ $booking->costume->character_name }}</a></p>
    <p>Ukuran: {{ $booking->size }}</p>
    <p>Tenant: {{ $booking->tenant->store_name }} ({{ $booking->tenant->city }})</p>
</section>

<section>
    <h2>Detail Sewa</h2>
    <table>
        <tbody>
            <tr>
                <th scope="row">Nama penyewa</th>
                <td>{{ $booking->booker_name }}</td>
            </tr>
            <tr>
                <th scope="row">Nomor identitas</th>
                <td>{{ $booking->identity_number }}</td>
            </tr>
            <tr>
                <th scope="row">Telepon</th>
                <td>{{ $booking->phone }}</td>
            </tr>
            <tr>
                <th scope="row">Alamat</th>
                <td>{{ $booking->address }}</td>
            </tr>
            <tr>
                <th scope="row">Tanggal sewa</th>
                <td>{{ tanggal_indonesia($booking->rental_date) }}</td>
            </tr>
            <tr>
                <th scope="row">Tanggal kembali</th>
                <td>{{ tanggal_indonesia($booking->return_date) }}</td>
            </tr>
            <tr>
                <th scope="row">Durasi</th>
                <td>{{ $booking->duration_days }} hari</td>
            </tr>
            <tr>
                <th scope="row">Metode pengiriman</th>
                <td>{{ $booking->shipping_method === 'courier' ? 'Kurir / Ekspedisi' : 'COD / Langsung' }}</td>
            </tr>
        </tbody>
    </table>
</section>

<section>
    <h2>Rincian Biaya</h2>
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
        <p>Belum ada transaksi pembayaran.</p>
    @else
        <table>
            <caption>Riwayat pembayaran</caption>
            <thead>
                <tr>
                    <th scope="col">ID transaksi</th>
                    <th scope="col">Metode</th>
                    <th scope="col">Nominal</th>
                    <th scope="col">Status</th>
                    <th scope="col">Waktu</th>
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
        <p><a href="{{ route('payment.show', $booking) }}">Lanjut ke pembayaran</a></p>
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
                    <th scope="row">Nomor resi</th>
                    <td>{{ $booking->returnItem->tracking_number ?: '-' }}</td>
                </tr>
                <tr>
                    <th scope="row">Lokasi COD</th>
                    <td>{{ $booking->returnItem->cod_location ?: '-' }}</td>
                </tr>
                <tr>
                    <th scope="row">Waktu COD</th>
                    <td>{{ $booking->returnItem->cod_time ?: '-' }}</td>
                </tr>
                <tr>
                    <th scope="row">Kondisi</th>
                    <td>{{ $booking->returnItem->condition ? $booking->returnItem->conditionLabel() : 'Menunggu verifikasi tenant' }}</td>
                </tr>
                <tr>
                    <th scope="row">Hari terlambat</th>
                    <td>{{ $booking->returnItem->days_late }}</td>
                </tr>
                <tr>
                    <th scope="row">Denda keterlambatan</th>
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

<section>
    <h2>Tindakan</h2>
    <ul>
        @if ($booking->canStartReturn() && ! $booking->returnItem)
            <li><a href="{{ route('returns.create', $booking) }}">Mulai Proses Pengembalian</a></li>
        @endif
        <li><a href="{{ route('catalog.index') }}">Lihat katalog lain</a></li>
        <li><a href="{{ route('dashboard') }}">Kembali ke dashboard</a></li>
    </ul>
</section>

<section>
    <h2>Ulasan</h2>

    @if ($canReview)
        <form method="POST" action="{{ route('reviews.store', $booking) }}">
            @csrf

            <p>
                <label for="rating">Rating (1-5)</label>
                <select id="rating" name="rating" required>
                    @for ($i = 1; $i <= 5; $i++)
                        <option value="{{ $i }}" @selected(old('rating') == $i)>{{ $i }}</option>
                    @endfor
                </select>
            </p>

            <p>
                <label for="comment">Komentar</label>
                <textarea id="comment" name="comment" rows="4">{{ old('comment') }}</textarea>
            </p>

            <p><button type="submit">Kirim Ulasan</button></p>
        </form>
    @elseif ($booking->status === App\Models\Booking::STATUS_COMPLETED)
        <p>Ulasan sudah dikirim atau tidak tersedia.</p>
    @else
        <p>Form ulasan tersedia setelah pesanan selesai.</p>
    @endif

    @if ($booking->reviews->isNotEmpty())
        <ul>
            @foreach ($booking->reviews as $review)
                <li>
                    <p><strong>{{ $review->reviewer?->name ?? 'Pengguna' }}</strong> - {{ $review->target === 'tenant' ? 'untuk tenant' : 'untuk customer' }}</p>
                    <p>Rating {{ $review->rating }}/5</p>
                    <p>{{ $review->comment ?: 'Tanpa komentar.' }}</p>
                </li>
            @endforeach
        </ul>
    @endif
</section>
@endsection
