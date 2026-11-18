@extends('layouts.app')

@section('title', 'Pengaturan Platform')

@section('content')
@include('partials.dashboard-nav')

<h1>Pengaturan Platform</h1>

<p>Nilai berikut masih bersifat sementara (placeholder) sampai kebijakan final ditetapkan.</p>

<form method="POST" action="{{ route('admin.settings.update') }}">
    @csrf

    <fieldset>
        <legend>Denda Keterlambatan</legend>

        <p>
            <label for="late_fee_per_day">Tarif denda per hari (Rupiah)</label>
            <input id="late_fee_per_day" name="late_fee_per_day" type="number" min="0" step="1" value="{{ old('late_fee_per_day', $settings['late_fee_per_day']) }}" required>
        </p>

        <p>
            <label for="max_late_fee">Maksimum denda (Rupiah)</label>
            <input id="max_late_fee" name="max_late_fee" type="number" min="0" step="1" value="{{ old('max_late_fee', $settings['max_late_fee']) }}" required>
        </p>
    </fieldset>

    <fieldset>
        <legend>Deposit dan Pengiriman</legend>

        <p>
            <label for="default_deposit">Deposit bawaan (Rupiah)</label>
            <input id="default_deposit" name="default_deposit" type="number" min="0" step="1" value="{{ old('default_deposit', $settings['default_deposit']) }}" required>
        </p>

        <p>
            <label for="shipping_fee">Biaya pengiriman kurir (Rupiah)</label>
            <input id="shipping_fee" name="shipping_fee" type="number" min="0" step="1" value="{{ old('shipping_fee', $settings['shipping_fee']) }}" required>
        </p>
    </fieldset>

    <fieldset>
        <legend>Durasi Sewa</legend>

        <p>
            <label for="min_rental_days">Minimal durasi sewa (hari)</label>
            <input id="min_rental_days" name="min_rental_days" type="number" min="1" max="30" step="1" value="{{ old('min_rental_days', $settings['min_rental_days']) }}" required>
        </p>

        <p>
            <label for="max_rental_days">Maksimal durasi sewa (hari)</label>
            <input id="max_rental_days" name="max_rental_days" type="number" min="1" max="90" step="1" value="{{ old('max_rental_days', $settings['max_rental_days']) }}" required>
        </p>
    </fieldset>

    <fieldset>
        <legend>Komisi Platform</legend>

        <p>
            <label for="platform_commission_percent">Komisi platform (%)</label>
            <input id="platform_commission_percent" name="platform_commission_percent" type="number" min="0" max="100" step="0.01" value="{{ old('platform_commission_percent', $settings['platform_commission_percent']) }}" required>
        </p>
    </fieldset>

    <p><button type="submit">Simpan Pengaturan</button></p>
</form>

<section>
    <h2>Catatan Kebijakan yang Belum Final</h2>
    <ul>
        <li>Payment gateway final (Midtrans/Xendit) belum ditentukan.</li>
        <li>Formula denda dan batas maksimum masih placeholder.</li>
        <li>Kebijakan pembatalan dan refund belum final.</li>
        <li>Interval backup database belum ditentukan.</li>
        <li>Prosedur verifikasi dokumen legal tenant belum final.</li>
        <li>Komisi platform belum final.</li>
        <li>Kompensasi kerusakan/kehilangan di luar deposit belum final.</li>
        <li>Kanal notifikasi final belum ditentukan.</li>
        <li>Kebijakan retry webhook pembayaran gagal belum ditentukan.</li>
    </ul>
</section>
@endsection
