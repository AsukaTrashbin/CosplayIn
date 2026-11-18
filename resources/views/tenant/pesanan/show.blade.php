@extends('layouts.app')

@section('title', 'Detail Pesanan '.$booking->booking_code)

@if ($booking->isActive())
    @push('head')
        <meta http-equiv="refresh" content="30">
    @endpush
@endif

@section('content')
@include('partials.dashboard-nav')

<h1>Pesanan {{ $booking->booking_code }}</h1>
<p>Status: <strong>{{ $booking->statusLabel() }}</strong></p>

<p><a href="{{ route('tenant.orders.index') }}">&laquo; Kembali ke daftar pesanan</a></p>

<section>
    <h2>Informasi Customer</h2>
    <table>
        <tbody>
            <tr>
                <th scope="row">Nama customer</th>
                <td>{{ $booking->customer->name }}</td>
            </tr>
            <tr>
                <th scope="row">Email</th>
                <td>{{ $booking->customer->email }}</td>
            </tr>
            <tr>
                <th scope="row">Nama penyewa (KTP)</th>
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
                <th scope="row">Foto KTP</th>
                <td><a href="{{ route('tenant.ktp.show', $booking) }}">Lihat KTP (akses terbatas)</a></td>
            </tr>
        </tbody>
    </table>
</section>

<section>
    <h2>Detail Kostum dan Sewa</h2>
    <img src="{{ $booking->costume->primaryImageUrl() }}" alt="Foto kostum {{ $booking->costume->character_name }}" width="300" height="200">
    <p>Kostum: {{ $booking->costume->character_name }}</p>
    <p>Ukuran: {{ $booking->size }}</p>
    <p>Tanggal sewa: {{ tanggal_indonesia($booking->rental_date) }}</p>
    <p>Tanggal kembali: {{ tanggal_indonesia($booking->return_date) }}</p>
    <p>Durasi: {{ $booking->duration_days }} hari</p>
    <p>Metode pengiriman: {{ $booking->shipping_method === 'courier' ? 'Kurir / Ekspedisi' : 'COD / Langsung' }}</p>
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
        <ul>
            @foreach ($booking->payments as $payment)
                <li>
                    {{ $payment->external_id ?? '-' }} -
                    {{ $payment->methodLabel() }} -
                    {{ rupiah($payment->amount) }} -
                    {{ $payment->statusLabel() }}
                    @if ($payment->paid_at)
                        ({{ $payment->paid_at->format('d/m/Y H:i') }})
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</section>

<section>
    <h2>Tindakan Tenant</h2>
    <ul>
        @if ($booking->status === App\Models\Booking::STATUS_WAITING_CONFIRMATION)
            <li>
                <form method="POST" action="{{ route('tenant.bookings.approve', $booking) }}">
                    @csrf
                    <button type="submit">Setujui Pengajuan</button>
                </form>
            </li>
            <li>
                <form method="POST" action="{{ route('tenant.bookings.reject', $booking) }}">
                    @csrf
                    <p>
                        <label for="alasan_penolakan">Alasan penolakan</label>
                        <input id="alasan_penolakan" name="alasan_penolakan" type="text" required>
                    </p>
                    <button type="submit">Tolak Pengajuan</button>
                </form>
            </li>
        @endif

        @if ($booking->status === App\Models\Booking::STATUS_PAID)
            <li>
                <form method="POST" action="{{ route('tenant.orders.ready', $booking) }}">
                    @csrf
                    <button type="submit">Tandai Siap Diproses / Sedang Disiapkan</button>
                </form>
            </li>
        @endif

        @if (in_array($booking->status, [App\Models\Booking::STATUS_PAID, App\Models\Booking::STATUS_READY_TO_PROCESS], true))
            <li>
                <form method="POST" action="{{ route('tenant.orders.rented', $booking) }}">
                    @csrf
                    <button type="submit">Tandai Sedang Disewa</button>
                </form>
            </li>
        @endif
    </ul>
</section>

@if ($booking->status === App\Models\Booking::STATUS_RETURNED && $booking->returnItem)
    <section>
        <h2>Verifikasi Pengembalian</h2>

        <table>
            <caption>Data pengembalian dari customer</caption>
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
            </tbody>
        </table>

        <p>
            Denda keterlambatan dihitung otomatis dari tarif {{ rupiah(config('cosplayin.late_fee.per_day')) }} per hari
            dengan maksimum {{ rupiah(config('cosplayin.late_fee.max')) }}.
            Klaim kerusakan/kehilangan dipotong dari deposit {{ rupiah($booking->deposit) }}.
        </p>

        <form method="POST" action="{{ route('tenant.returns.verify', $booking) }}">
            @csrf

            <p>
                <label for="tanggal_kembali_aktual">Tanggal kembali aktual</label>
                <input id="tanggal_kembali_aktual" name="tanggal_kembali_aktual" type="date" max="{{ now()->toDateString() }}" value="{{ old('tanggal_kembali_aktual', now()->toDateString()) }}" required>
            </p>

            <fieldset>
                <legend>Kondisi Kostum</legend>

                <p>
                    <input id="kondisi_good" name="kondisi" type="radio" value="good" @checked(old('kondisi', 'good') === 'good') required>
                    <label for="kondisi_good">Baik dan lengkap (deposit dikembalikan penuh)</label>
                </p>
                <p>
                    <input id="kondisi_damaged" name="kondisi" type="radio" value="damaged_lost" @checked(old('kondisi') === 'damaged_lost')>
                    <label for="kondisi_damaged">Rusak atau hilang</label>
                </p>
            </fieldset>

            <p>
                <label for="klaim_kerusakan">Nominal klaim kerusakan/kehilangan (wajib jika rusak/hilang)</label>
                <input id="klaim_kerusakan" name="klaim_kerusakan" type="number" min="0" step="1" value="{{ old('klaim_kerusakan', 0) }}">
            </p>

            <p>
                <label for="catatan_tenant">Catatan tenant</label>
                <textarea id="catatan_tenant" name="catatan_tenant" rows="3">{{ old('catatan_tenant') }}</textarea>
            </p>

            <p><button type="submit">Verifikasi Pengembalian</button></p>
        </form>
    </section>
@endif

@if ($booking->returnItem)
    <section>
        <h2>Hasil Verifikasi Pengembalian</h2>
        <dl>
            <dt>Kondisi</dt>
            <dd>{{ $booking->returnItem->condition ? $booking->returnItem->conditionLabel() : 'Belum diverifikasi' }}</dd>
            <dt>Hari terlambat</dt>
            <dd>{{ $booking->returnItem->days_late }}</dd>
            <dt>Denda keterlambatan</dt>
            <dd>{{ rupiah($booking->returnItem->late_fee) }}</dd>
            <dt>Klaim kerusakan</dt>
            <dd>{{ rupiah($booking->returnItem->damage_claim) }}</dd>
            <dt>Deposit dipotong</dt>
            <dd>{{ rupiah($booking->returnItem->deposit_deduction) }}</dd>
            <dt>Deposit dikembalikan</dt>
            <dd>{{ rupiah($booking->returnItem->deposit_refunded) }}</dd>
        </dl>
    </section>
@endif

<section>
    <h2>Ulasan</h2>

    @if ($canReview)
        <form method="POST" action="{{ route('tenant.reviews.store', $booking) }}">
            @csrf

            <p>
                <label for="rating">Rating customer (1-5)</label>
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
        <p>Ulasan dapat diberikan setelah pesanan selesai.</p>
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
