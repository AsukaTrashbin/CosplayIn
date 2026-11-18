@extends('layouts.app')

@section('title', 'Kebijakan Privasi')

@section('content')
<article>
    <h1>Kebijakan Privasi CosplayIn</h1>
    <p>Terakhir diperbarui: {{ date('d/m/Y') }}</p>

    <section>
        <h2>1. Data yang Kami Kumpulkan</h2>
        <ul>
            <li>Nama, username, email, dan password terenkripsi.</li>
            <li>Nomor telepon, alamat, dan data identitas untuk keperluan verifikasi penyewaan.</li>
            <li>Foto KTP yang disimpan di penyimpanan privat dan hanya dapat diakses admin serta tenant terkait.</li>
            <li>Riwayat transaksi, pembayaran, ulasan, dan data pengembalian.</li>
        </ul>
    </section>

    <section>
        <h2>2. Penggunaan Data</h2>
        <p>Data digunakan untuk memproses penyewaan, verifikasi identitas, pembayaran, pengiriman, penagihan denda, dan komunikasi terkait transaksi.</p>
    </section>

    <section>
        <h2>3. Penyimpanan dan Keamanan</h2>
        <p>Password disimpan menggunakan hashing bcrypt bawaan Laravel. Sesi dashboard dienkripsi. Data transaksi disimpan minimal 5 tahun dan tidak dihapus secara permanen.</p>
    </section>

    <section>
        <h2>4. Berbagi Data</h2>
        <p>Data penyewa dibagikan kepada tenant terkait sejauh diperlukan untuk memproses pesanan. Data tidak dijual kepada pihak ketiga.</p>
    </section>

    <section>
        <h2>5. Hak Pengguna</h2>
        <p>Pengguna dapat meminta pembaruan data melalui menu profil atau menghubungi admin. Penghapusan data transaksi dibatasi oleh kewajiban penyimpanan minimal 5 tahun.</p>
    </section>

    <section>
        <h2>6. Kanal Notifikasi</h2>
        <p>Saat ini notifikasi dikirim melalui in-app dan email. Kanal final (email, SMS, WhatsApp, atau in-app) masih dalam pembahasan.</p>
    </section>
</article>
@endsection
