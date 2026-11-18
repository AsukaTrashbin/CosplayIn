@extends('layouts.app')

@section('title', 'Ajukan Sewa '.$costume->character_name)

@section('content')
<h1>Ajukan Sewa: {{ $costume->character_name }}</h1>

<p><a href="{{ route('catalog.show', $costume) }}">&laquo; Kembali ke detail kostum</a></p>

<section>
    <h2>Ringkasan Kostum</h2>
    <img src="{{ $costume->primaryImageUrl() }}" alt="Foto kostum {{ $costume->character_name }}" width="300" height="200">
    <p>Tenant: {{ $costume->tenant->store_name }} ({{ $costume->city }})</p>
    <p>Harga sewa: {{ rupiah($costume->price_per_day) }} / hari</p>
    <p>Deposit: {{ rupiah($costume->deposit > 0 ? $costume->deposit : config('cosplayin.deposit')) }}</p>
    <p>Biaya pengiriman (jika kurir): {{ rupiah(config('cosplayin.shipping_fee')) }}</p>
    <p>Durasi sewa: minimal {{ config('cosplayin.rental.min_days') }} hari, maksimal {{ config('cosplayin.rental.max_days') }} hari.</p>
    <p>Total akhir dihitung oleh server berdasarkan tanggal, ukuran, deposit, dan metode pengiriman.</p>
</section>

<section>
    <h2>Formulir Penyewaan</h2>

    <form method="POST" action="{{ route('booking.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="costume_id" value="{{ $costume->id }}">

        <fieldset>
            <legend>Identitas Penyewa</legend>

            <p>
                <label for="nama_penyewa">Nama penyewa (sesuai KTP)</label>
                <input id="nama_penyewa" name="nama_penyewa" type="text" value="{{ old('nama_penyewa', auth()->user()->name) }}" required>
            </p>

            <p>
                <label for="nomor_identitas">Nomor identitas (KTP)</label>
                <input id="nomor_identitas" name="nomor_identitas" type="text" value="{{ old('nomor_identitas') }}" inputmode="numeric" required>
            </p>

            <p>
                <label for="telepon">Nomor telepon</label>
                <input id="telepon" name="telepon" type="tel" value="{{ old('telepon') }}" required>
            </p>

            <p>
                <label for="alamat">Alamat lengkap</label>
                <textarea id="alamat" name="alamat" rows="3" required>{{ old('alamat') }}</textarea>
            </p>

            <p>
                <label for="ktp">Unggah foto KTP (JPG, PNG, atau PDF, maksimal 2 MB)</label>
                <input id="ktp" name="ktp" type="file" accept=".jpg,.jpeg,.png,.pdf" required>
                <br>
                <small>Foto KTP disimpan di penyimpanan privat dan hanya dapat diakses admin serta tenant terkait.</small>
            </p>
        </fieldset>

        <fieldset>
            <legend>Detail Sewa</legend>

            <p>
                <label for="tanggal_mulai">Tanggal mulai sewa</label>
                <input id="tanggal_mulai" name="tanggal_mulai" type="date" min="{{ now()->toDateString() }}" value="{{ old('tanggal_mulai', now()->toDateString()) }}" required>
            </p>

            <p>
                <label for="tanggal_selesai">Tanggal kembali</label>
                <input id="tanggal_selesai" name="tanggal_selesai" type="date" min="{{ now()->addDay()->toDateString() }}" value="{{ old('tanggal_selesai', now()->addDay()->toDateString()) }}" required>
            </p>

            <p>
                <label for="ukuran">Ukuran kostum</label>
                <select id="ukuran" name="ukuran" required>
                    @foreach (App\Models\Costume::SIZES as $size)
                        @php($stock = $sizes->get($size))
                        <option value="{{ $size }}" @selected(old('ukuran') === $size)>
                            {{ $size }} - stok {{ $stock?->stock ?? 0 }}
                        </option>
                    @endforeach
                </select>
            </p>

            <fieldset>
                <legend>Metode pengiriman</legend>

                <p>
                    <input id="metode_courier" name="metode_pengiriman" type="radio" value="courier" @checked(old('metode_pengiriman', 'courier') === 'courier') required>
                    <label for="metode_courier">Kurir / ekspedisi (dikenakan biaya pengiriman)</label>
                </p>

                <p>
                    <input id="metode_cod" name="metode_pengiriman" type="radio" value="cod" @checked(old('metode_pengiriman') === 'cod')>
                    <label for="metode_cod">COD / ambil dan kembalikan langsung (tanpa biaya pengiriman)</label>
                </p>
            </fieldset>
        </fieldset>

        <p>
            Dengan mengajukan sewa, Anda menyatakan data yang diisi benar dan menyetujui
            <a href="{{ route('pages.terms') }}">Syarat dan Ketentuan</a>.
        </p>

        <p><button type="submit">Ajukan Sewa</button></p>
    </form>
</section>
@endsection
