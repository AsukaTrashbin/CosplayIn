@extends('layouts.app')

@section('title', 'Pengembalian '.$booking->booking_code)

@section('content')
<h1>Proses Pengembalian</h1>

<p><a href="{{ route('orders.show', $booking) }}">&laquo; Kembali ke pesanan</a></p>

<section>
    <h2>Ringkasan Pesanan</h2>
    <p>Kode: {{ $booking->booking_code }}</p>
    <p>Kostum: {{ $booking->costume->character_name }} (ukuran {{ $booking->size }})</p>
    <p>Tanggal sewa: {{ tanggal_indonesia($booking->rental_date) }} - {{ tanggal_indonesia($booking->return_date) }}</p>
    <p>Deposit: {{ rupiah($booking->deposit) }}</p>
</section>

<section>
    <h2>Formulir Pengembalian</h2>

    <p>Denda keterlambatan dihitung otomatis oleh sistem dengan tarif {{ rupiah(config('cosplayin.late_fee.per_day')) }} per hari dan maksimum {{ rupiah(config('cosplayin.late_fee.max')) }} (dapat berubah sesuai pengaturan admin).</p>

    <form method="POST" action="{{ route('returns.store', $booking) }}">
        @csrf

        <fieldset>
            <legend>Metode Pengembalian</legend>

            <p>
                <input id="metode_courier" name="metode" type="radio" value="courier" @checked(old('metode', 'courier') === 'courier') required>
                <label for="metode_courier">Kurir / ekspedisi</label>
            </p>

            <p>
                <label for="nomor_resi">Nomor resi (wajib untuk kurir)</label>
                <input id="nomor_resi" name="nomor_resi" type="text" value="{{ old('nomor_resi') }}">
            </p>

            <p>
                <input id="metode_cod" name="metode" type="radio" value="cod" @checked(old('metode') === 'cod')>
                <label for="metode_cod">COD / ketemu langsung</label>
            </p>

            <p>
                <label for="lokasi_cod">Lokasi pertemuan (wajib untuk COD)</label>
                <input id="lokasi_cod" name="lokasi_cod" type="text" value="{{ old('lokasi_cod') }}">
            </p>

            <p>
                <label for="waktu_cod">Waktu pertemuan (wajib untuk COD)</label>
                <input id="waktu_cod" name="waktu_cod" type="text" value="{{ old('waktu_cod') }}" placeholder="Contoh: 20/11/2026 14:00">
            </p>
        </fieldset>

        <p><button type="submit">Ajukan Pengembalian</button></p>
    </form>
</section>
@endsection
