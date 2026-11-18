@extends('layouts.app')

@section('title', 'Pembayaran '.$booking->booking_code)

@section('content')
<h1>Pembayaran Pesanan {{ $booking->booking_code }}</h1>

<p><a href="{{ route('orders.show', $booking) }}">&laquo; Kembali ke detail pesanan</a></p>

<section>
    <h2>Rincian Tagihan</h2>
    <table>
        <caption>Tagihan dihitung oleh server</caption>
        <tbody>
            <tr>
                <th scope="row">Kostum</th>
                <td>{{ $booking->costume->character_name }} (ukuran {{ $booking->size }})</td>
            </tr>
            <tr>
                <th scope="row">Durasi</th>
                <td>{{ $booking->duration_days }} hari ({{ tanggal_indonesia($booking->rental_date) }} - {{ tanggal_indonesia($booking->return_date) }})</td>
            </tr>
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
    <h2>Status Pembayaran</h2>
    <p>Status pesanan: <strong>{{ $booking->statusLabel() }}</strong></p>

    @if ($latestPayment)
        <table>
            <caption>Pembayaran terakhir</caption>
            <tbody>
                <tr>
                    <th scope="row">ID transaksi</th>
                    <td>{{ $latestPayment->external_id ?? '-' }}</td>
                </tr>
                <tr>
                    <th scope="row">Metode</th>
                    <td>{{ $latestPayment->methodLabel() }}</td>
                </tr>
                <tr>
                    <th scope="row">Nominal</th>
                    <td>{{ rupiah($latestPayment->amount) }}</td>
                </tr>
                <tr>
                    <th scope="row">Status</th>
                    <td>{{ $latestPayment->statusLabel() }}</td>
                </tr>
                <tr>
                    <th scope="row">Waktu bayar</th>
                    <td>{{ $latestPayment->paid_at?->format('d/m/Y H:i') ?? '-' }}</td>
                </tr>
            </tbody>
        </table>
    @else
        <p>Belum ada transaksi pembayaran.</p>
    @endif

    @if (in_array($booking->status, [App\Models\Booking::STATUS_WAITING_PAYMENT, App\Models\Booking::STATUS_FAILED], true))
        <form method="POST" action="{{ route('payment.store', $booking) }}">
            @csrf

            <fieldset>
                <legend>Pilih Metode Pembayaran</legend>

                <p>
                    <input id="metode_transfer" name="metode" type="radio" value="transfer" @checked(old('metode') === 'transfer') required>
                    <label for="metode_transfer">Transfer Bank</label>
                </p>
                <p>
                    <input id="metode_ewallet" name="metode" type="radio" value="ewallet" @checked(old('metode') === 'ewallet')>
                    <label for="metode_ewallet">E-Wallet</label>
                </p>
                <p>
                    <input id="metode_qris" name="metode" type="radio" value="qris" @checked(old('metode') === 'qris')>
                    <label for="metode_qris">QRIS</label>
                </p>
            </fieldset>

            <p><button type="submit">Buat Instruksi Pembayaran</button></p>
        </form>

        @if ($allowMockSuccess)
            <form method="POST" action="{{ route('payment.mock', $booking) }}">
                @csrf
                <p>
                    <button type="submit">Simulasi Pembayaran Berhasil (Mock)</button>
                </p>
                <p><small>Tombol ini hanya tersedia selama payment gateway belum final. Bisa dimatikan lewat MOCK_PAYMENT_ALLOW_SUCCESS.</small></p>
            </form>
        @endif
    @endif
</section>

<section>
    <h2>Instruksi Pembayaran Manual</h2>

    <h3>Transfer Bank</h3>
    <p>{{ $bankAccounts['transfer']['bank'] }} - {{ $bankAccounts['transfer']['number'] }} a.n. {{ $bankAccounts['transfer']['holder'] }}</p>

    <h3>E-Wallet</h3>
    <p>{{ $bankAccounts['ewallet']['provider'] }} - {{ $bankAccounts['ewallet']['number'] }} a.n. {{ $bankAccounts['ewallet']['holder'] }}</p>

    <h3>QRIS</h3>
    <p>{{ $bankAccounts['qris']['note'] }}</p>

    <p>Setelah membayar, admin akan memverifikasi pembayaran. Anda dapat memantau status pada halaman pesanan.</p>
</section>

@if ($latestPayment && $latestPayment->status === App\Models\Payment::STATUS_SUCCESS)
    <section>
        <h2>Tanda Terima Pembayaran</h2>
        <p>Pembayaran untuk pesanan <strong>{{ $booking->booking_code }}</strong> telah diterima sebesar <strong>{{ rupiah($latestPayment->amount) }}</strong>.</p>
        <p>Tanggal pembayaran: {{ $latestPayment->paid_at?->format('d/m/Y H:i') }}</p>
        <p>Status pesanan: {{ $booking->statusLabel() }}</p>
    </section>
@endif
@endsection
