@extends('layouts.app')

@section('title', 'Ajukan Sewa '.$costume->character_name)

@section('content')
<div class="ci-container py-4 pb-5">

    <a href="{{ route('catalog.show', $costume) }}" class="link-arrow d-inline-block mb-4">&larr; Kembali ke detail kostum</a>

    <h1 class="catalog-title mb-3">Atur momen cosplay-mu.</h1>

    <div class="booking-steps mb-5">
        <span class="step-active">1 Pengajuan</span>
        <span class="step-line"></span>
        <span>2 Persetujuan tenant</span>
        <span class="step-line"></span>
        <span>3 Pembayaran</span>
    </div>

    <form method="POST" action="{{ route('booking.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="costume_id" value="{{ $costume->id }}">

        <div class="row g-4 align-items-start">

            {{-- Kiri: form --}}
            <div class="col-lg-7">
                <div class="search-card p-4 p-lg-5">
                    <h2 class="section-title fs-3 mb-2">Detail penyewaan</h2>
                    <p class="text-muted-ci mb-4">Lengkapi data, tanggal (lama sewa {{ config('cosplayin.rental.min_days') }}-{{ config('cosplayin.rental.max_days') }} hari), dan foto KTP. Tenant akan menyetujui pengajuanmu.</p>

                    {{-- Nama pemesan --}}
                    <div class="mb-4">
                        <label for="nama_penyewa" class="form-label auth-label">Nama pemesan</label>
                        <input id="nama_penyewa" name="nama_penyewa" type="text"
                               class="form-control auth-input @error('nama_penyewa') is-invalid @enderror"
                               value="{{ old('nama_penyewa', auth()->user()->name) }}" required>
                        @error('nama_penyewa')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Tanggal --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="tanggal_mulai" class="form-label auth-label">Tanggal sewa</label>
                            <input id="tanggal_mulai" name="tanggal_mulai" type="date"
                                   class="form-control auth-input @error('tanggal_mulai') is-invalid @enderror"
                                   min="{{ now()->toDateString() }}"
                                   value="{{ old('tanggal_mulai', now()->toDateString()) }}" required>
                            @error('tanggal_mulai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="tanggal_selesai" class="form-label auth-label">Tanggal pengembalian</label>
                            <input id="tanggal_selesai" name="tanggal_selesai" type="date"
                                   class="form-control auth-input @error('tanggal_selesai') is-invalid @enderror"
                                   min="{{ now()->addDay()->toDateString() }}"
                                   value="{{ old('tanggal_selesai', now()->addDay()->toDateString()) }}" required>
                            @error('tanggal_selesai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Ukuran --}}
                    <div class="mb-4">
                        <label for="ukuran" class="form-label auth-label">Ukuran</label>
                        <select id="ukuran" name="ukuran" class="form-select auth-input @error('ukuran') is-invalid @enderror" required>
                            @foreach (App\Models\Costume::SIZES as $size)
                                @php($stock = $sizes->get($size))
                                <option value="{{ $size }}" @selected(old('ukuran') === $size) @disabled(($stock?->stock ?? 0) < 1)>
                                    {{ $size }} - stok {{ $stock?->stock ?? 0 }}
                                </option>
                            @endforeach
                        </select>
                        @error('ukuran')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Foto KTP --}}
                    <div class="mb-4">
                        <label for="ktp" class="form-label auth-label">Foto KTP</label>
                        <input id="ktp" name="ktp" type="file" accept=".jpg,.jpeg,.png,.pdf"
                               class="form-control auth-input @error('ktp') is-invalid @enderror" required>
                        @error('ktp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="booking-hint mt-2">JPG, PNG, atau PDF, maksimal 2 MB. Disimpan privat dan hanya dapat diakses admin serta tenant terkait.</div>
                    </div>

                    {{-- Alamat & telepon (wajib di backend, tidak ada di screenshot, ditaruh setelah KTP) --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="telepon" class="form-label auth-label">Nomor telepon</label>
                            <input id="telepon" name="telepon" type="tel"
                                   class="form-control auth-input" value="{{ old('telepon') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="nomor_identitas" class="form-label auth-label">Nomor KTP</label>
                            <input id="nomor_identitas" name="nomor_identitas" type="text"
                                   class="form-control auth-input" value="{{ old('nomor_identitas') }}" inputmode="numeric" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="alamat" class="form-label auth-label">Alamat lengkap</label>
                        <textarea id="alamat" name="alamat" rows="3"
                                  class="form-control auth-input" required>{{ old('alamat') }}</textarea>
                    </div>

                    {{-- Metode pengiriman --}}
                    <div class="mb-4">
                        <div class="auth-label mb-2">Metode pengiriman</div>
                        <div class="form-check mb-2">
                            <input id="metode_courier" name="metode_pengiriman" type="radio" value="courier"
                                   class="form-check-input catalog-checkbox"
                                   @checked(old('metode_pengiriman', 'courier') === 'courier') required>
                            <label for="metode_courier" class="form-check-label auth-link">
                                Kurir / ekspedisi (biaya pengiriman {{ rupiah(config('cosplayin.shipping_fee')) }})
                            </label>
                        </div>
                        <div class="form-check">
                            <input id="metode_cod" name="metode_pengiriman" type="radio" value="cod"
                                   class="form-check-input catalog-checkbox"
                                   @checked(old('metode_pengiriman') === 'cod')>
                            <label for="metode_cod" class="form-check-label auth-link">
                                COD / ambil dan kembalikan langsung (tanpa biaya pengiriman)
                            </label>
                        </div>
                    </div>

                    <div class="booking-availability mb-4">
                        &check; Kostum tersedia pada tanggal yang kamu pilih.
                    </div>

                    {{-- Persetujuan --}}
                    <div class="form-check mb-4">
                        <input id="agree" name="agree" type="checkbox" value="1"
                               class="form-check-input catalog-checkbox" required>
                        <label for="agree" class="form-check-label auth-link">
                            Saya menyetujui <a href="{{ route('pages.terms') }}" class="auth-link fw-semibold">syarat dan ketentuan</a> penyewaan dan pengembalian.
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 auth-submit">Ajukan sewa &rarr;</button>
                </div>
            </div>

            {{-- Kanan: ringkasan --}}
            <div class="col-lg-5">
                <div class="search-card p-4 p-lg-5">
                    <h2 class="section-title fs-3 mb-4">Ringkasan pesanan</h2>

                    <div class="d-flex gap-3 align-items-start pb-4 mb-3 border-bottom">
                        <div class="booking-summary-media">
                            <img src="{{ $costume->primaryImageUrl() }}" alt="Foto kostum {{ $costume->character_name }}">
                        </div>
                        <div>
                            <div class="fw-semibold fs-5 mb-1">{{ $costume->character_name }}</div>
                            <div class="booking-hint mb-2">{{ $costume->category?->name ?? 'Tanpa kategori' }} &middot; Ukuran {{ old('ukuran', 'M') }}</div>
                            <div class="booking-hint">{{ $costume->tenant->store_name }}, {{ $costume->city }}</div>
                        </div>
                    </div>

                    <div class="booking-summary-row">
                        <span class="label">Tanggal sewa</span>
                        <span class="value" id="summary-tanggal">{{ now()->toDateString() }}</span>
                    </div>
                    <div class="booking-summary-row">
                        <span class="label">Harga / hari</span>
                        <span class="value">{{ rupiah($costume->price_per_day) }}</span>
                    </div>
                    <div class="booking-summary-row">
                        <span class="label">Deposit (dikembalikan)</span>
                        <span class="value">{{ rupiah($costume->deposit > 0 ? $costume->deposit : config('cosplayin.deposit')) }}</span>
                    </div>
                    <div class="booking-summary-row">
                        <span class="label">Ongkir</span>
                        <span class="value">{{ rupiah(config('cosplayin.shipping_fee')) }}</span>
                    </div>

                    <hr class="sidebar-divider my-4">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-semibold">Total pembayaran</span>
                        <span class="booking-total">Dihitung server</span>
                    </div>
                    <p class="booking-hint mb-0">Harga sudah termasuk seluruh aksesori. Total akhir dihitung oleh server berdasarkan tanggal, ukuran, deposit, dan metode pengiriman.</p>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection