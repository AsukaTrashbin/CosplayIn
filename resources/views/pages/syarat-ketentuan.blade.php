@extends('layouts.app')

@section('title', 'Syarat dan Ketentuan')

@section('content')
<article>
    <h1>Syarat dan Ketentuan CosplayIn</h1>
    <p>Terakhir diperbarui: {{ date('d/m/Y') }}</p>

    <section>
        <h2>1. Definisi Layanan</h2>
        <p>CosplayIn adalah platform yang mempertemukan customer penyewa kostum cosplay dengan tenant penyedia kostum. CosplayIn tidak memiliki kostum yang ditawarkan tenant.</p>
    </section>

    <section>
        <h2>2. Akun Pengguna</h2>
        <p>Pengguna wajib memberikan data yang benar, menjaga kerahasiaan password, dan memverifikasi email. Tenant wajib menunggu persetujuan admin sebelum dapat mengelola kostum.</p>
    </section>

    <section>
        <h2>3. Pemesanan dan Pembayaran</h2>
        <p>Pemesanan hanya dapat dilakukan oleh customer yang sudah login dan terverifikasi. Total biaya dihitung oleh server berdasarkan harga kostum, durasi, deposit, dan biaya pengiriman.</p>
    </section>

    <section>
        <h2>4. Pembatalan dan Refund</h2>
        <p>{{ config('cosplayin.cancellation.policy_text') }}</p>
    </section>

    <section>
        <h2>5. Pengembalian, Keterlambatan, dan Kerusakan</h2>
        <p>Keterlambatan pengembalian dikenakan denda per hari sesuai pengaturan platform. Kostum yang rusak atau hilang dapat diklaim dari deposit. Kebijakan kompensasi final masih dalam pembahasan.</p>
    </section>

    <section>
        <h2>6. Ulasan</h2>
        <p>Customer dan tenant dapat saling memberikan ulasan setelah transaksi berstatus selesai. Ulasan tidak boleh mengandung ujaran kebencian atau informasi palsu.</p>
    </section>

    <section>
        <h2>7. Data Transaksi</h2>
        <p>Data transaksi disimpan minimal 5 tahun untuk keperluan audit dan kepatuhan hukum.</p>
    </section>

    <section>
        <h2>8. Perubahan Ketentuan</h2>
        <p>CosplayIn dapat memperbarui syarat dan ketentuan ini. Perubahan akan diinformasikan melalui halaman ini.</p>
    </section>
</article>
@endsection
