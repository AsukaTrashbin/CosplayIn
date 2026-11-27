@extends('layouts.app')

@section('title', 'Pembayaran '.$booking->booking_code)

@section('content')
<div class="ci-container py-4 pb-5">

    <a href="{{ route('orders.show', $booking) }}" class="link-arrow d-inline-block mb-4">&larr; Kembali ke detail sewa</a>

    <h1 class="catalog-title mb-3">Satu langkah lagi, siap tampil.</h1>

    <div class="booking-steps mb-5">
        <span>1 Pengajuan</span>
        <span class="step-line"></span>
        <span>2 Persetujuan tenant</span>
        <span class="step-line"></span>
        <span class="step-active">3 Pembayaran</span>
    </div>

    <div class="row g-4 align-items-start">

        {{-- Kiri: metode pembayaran --}}
        <div class="col-lg-7">
            <div class="search-card p-4 p-lg-5">
                <h2 class="section-title fs-3 mb-4">Pilih metode pembayaran</h2>

                {{-- Ringkas total --}}
                <div class="pay-summary-box mb-4">
                    <div class="text-muted-ci small mb-2">
                        {{ $booking->user->name ?? auth()->user()->name }} &middot; {{ $booking->booking_code }}
                    </div>
                    <div class="pay-total">{{ rupiah($booking->total) }}</div>
                </div>

                @if (in_array($booking->status, [App\Models\Booking::STATUS_WAITING_PAYMENT, App\Models\Booking::STATUS_FAILED], true))
                    <form method="POST" action="{{ route('payment.store', $booking) }}">
                        @csrf

                        <div class="d-flex flex-column gap-3 mb-4">
                            <label class="pay-method is-selected">
                                <input type="radio" name="metode" value="transfer" @checked(old('metode', 'transfer') === 'transfer') required>
                                <div>
                                    <div class="pay-method-title">Transfer Bank</div>
                                    <div class="pay-method-desc">BCA, BNI, Mandiri, BRI</div>
                                </div>
                                <span class="pay-method-chevron">&#8964;</span>
                            </label>

                            <label class="pay-method">
                                <input type="radio" name="metode" value="ewallet" @checked(old('metode') === 'ewallet')>
                                <div>
                                    <div class="pay-method-title">E-Wallet</div>
                                    <div class="pay-method-desc">GoPay, OVO, DANA</div>
                                </div>
                                <span class="pay-method-chevron">&#8964;</span>
                            </label>

                            <label class="pay-method">
                                <input type="radio" name="metode" value="qris" @checked(old('metode') === 'qris')>
                                <div>
                                    <div class="pay-method-title">QRIS</div>
                                    <div class="pay-method-desc">Scan dari aplikasi pembayaran kamu</div>
                                </div>
                                <span class="pay-method-chevron">&#8964;</span>
                            </label>
                        </div>

                        <p class="detail-small-note mb-4">Pembayaran diproses aman. Detail transfer muncul setelah lanjut.</p>

                        <button type="submit" class="btn btn-primary w-100 auth-submit">Lanjut ke pembayaran &rarr;</button>
                    </form>

                    @if ($allowMockSuccess)
                        <form method="POST" action="{{ route('payment.mock', $booking) }}" class="mt-3">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary w-100 rounded-pill">Simulasi pembayaran berhasil (mock)</button>
                            <p class="detail-small-note mt-2 mb-0">Hanya tersedia selama payment gateway belum final. Bisa dimatikan lewat MOCK_PAYMENT_ALLOW_SUCCESS.</p>
                        </form>
                    @endif
                @endif

                {{-- Riwayat transaksi --}}
                @if ($latestPayment)
                    <div class="mt-4 pt-4 border-top">
                        <div class="fw-semibold mb-2">Pembayaran terakhir</div>
                        <div class="booking-summary-row"><span class="label">ID transaksi</span><span class="value">{{ $latestPayment->external_id ?? '-' }}</span></div>
                        <div class="booking-summary-row"><span class="label">Metode</span><span class="value">{{ $latestPayment->methodLabel() }}</span></div>
                        <div class="booking-summary-row"><span class="label">Nominal</span><span class="value">{{ rupiah($latestPayment->amount) }}</span></div>
                        <div class="booking-summary-row"><span class="label">Status</span><span class="value">{{ $latestPayment->statusLabel() }}</span></div>
                        <div class="booking-summary-row"><span class="label">Waktu bayar</span><span class="value">{{ $latestPayment->paid_at?->format('d/m/Y H:i') ?? '-' }}</span></div>
                    </div>
                @endif

                {{-- Instruksi manual --}}
                <div class="mt-4 pt-4 border-top">
                    <div class="fw-semibold mb-2">Instruksi pembayaran manual</div>
                    <p class="mb-1"><span class="text-muted-ci">Transfer Bank:</span> {{ $bankAccounts['transfer']['bank'] }} - {{ $bankAccounts['transfer']['number'] }} a.n. {{ $bankAccounts['transfer']['holder'] }}</p>
                    <p class="mb-1"><span class="text-muted-ci">E-Wallet:</span> {{ $bankAccounts['ewallet']['provider'] }} - {{ $bankAccounts['ewallet']['number'] }} a.n. {{ $bankAccounts['ewallet']['holder'] }}</p>
                    <p class="mb-2"><span class="text-muted-ci">QRIS:</span> {{ $bankAccounts['qris']['note'] }}</p>
                    <p class="detail-small-note mb-0">Setelah membayar, admin akan memverifikasi pembayaran. Pantau status pada halaman pesanan.</p>
                </div>

                @if ($latestPayment && $latestPayment->status === App\Models\Payment::STATUS_SUCCESS)
                    <div class="mt-4 pt-4 border-top">
                        <div class="fw-semibold mb-2">Tanda terima pembayaran</div>
                        <p class="mb-1">Pembayaran untuk pesanan <strong>{{ $booking->booking_code }}</strong> telah diterima sebesar <strong>{{ rupiah($latestPayment->amount) }}</strong>.</p>
                        <p class="mb-1">Tanggal pembayaran: {{ $latestPayment->paid_at?->format('d/m/Y H:i') }}</p>
                        <p class="mb-0">Status pesanan: {{ $booking->statusLabel() }}</p>
                    </div>
                @endif
            </div>

            <p class="detail-small-note mt-4 mb-0">Butuh bantuan? Hubungi tenant atau pusat bantuan.</p>
        </div>

        {{-- Kanan: ringkasan --}}
        <div class="col-lg-5">
            <div class="search-card p-4 p-lg-5">
                <h2 class="section-title fs-3 mb-4">Ringkasan pesanan</h2>

                <div class="d-flex gap-3 align-items-start pb-4 mb-3 border-bottom">
                    <div style="width: 88px; height: 112px; flex-shrink: 0; border-radius: .75rem; overflow: hidden; background: var(--ci-placeholder);">
                        <img src="{{ $booking->costume->primaryImageUrl() }}" alt="Foto kostum {{ $booking->costume->character_name }}"
                             style="width: 100%; height: 100%; object-fit: cover; display: block;">
                    </div>
                    <div style="min-width: 0;">
                        <div class="fw-semibold fs-5 mb-1">{{ $booking->costume->character_name }}</div>
                        <div class="booking-hint mb-2">{{ $booking->costume->category?->name ?? 'Tanpa kategori' }} &middot; Ukuran {{ $booking->size }}</div>
                        <div class="booking-hint">{{ $booking->costume->tenant->store_name }}, {{ $booking->costume->city }}</div>
                    </div>
                </div>

                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted-ci">Tanggal sewa</span>
                    <span class="fw-medium">{{ tanggal_indonesia($booking->rental_date) }}</span>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted-ci">Durasi</span>
                    <span class="fw-medium">{{ $booking->duration_days }} hari</span>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted-ci">Harga sewa</span>
                    <span class="fw-medium">{{ rupiah($booking->rental_price) }}</span>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted-ci">Deposit (dikembalikan)</span>
                    <span class="fw-medium">{{ rupiah($booking->deposit) }}</span>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted-ci">Ongkir</span>
                    <span class="fw-medium">{{ rupiah($booking->shipping_fee) }}</span>
                </div>

                <hr class="sidebar-divider my-4">

                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Total pembayaran</span>
                    <span class="pay-total">{{ rupiah($booking->total) }}</span>
                </div>
                <p class="booking-hint mt-3 mb-0">Harga sudah termasuk seluruh aksesori.</p>
            </div>
        </div>

    </div>
</div>
@endsection