@extends('layouts.app')

@section('title', $costume->character_name)

@section('content')
<article>
    <p><a href="{{ route('catalog.index') }}">&laquo; Kembali ke katalog</a></p>

    <h1>{{ $costume->character_name }}</h1>
    <p>Tenant: <a href="{{ route('tenants.show', $costume->tenant) }}">{{ $costume->tenant->store_name }}</a> ({{ $costume->tenant->city }})</p>
    <p>Kategori: {{ $costume->category?->name ?? 'Tanpa kategori' }} | Gender: {{ $costume->genderLabel() }} | Kota: {{ $costume->city }}</p>
    <p>Rating rata-rata: {{ $averageRating > 0 ? $averageRating.' / 5' : 'Belum ada rating' }}</p>

    <figure>
        <img src="{{ $costume->primaryImageUrl() }}" alt="Foto utama kostum {{ $costume->character_name }}" width="600" height="400">
        <figcaption>Foto utama</figcaption>
    </figure>

    @if ($costume->images->count() > 1)
        <section>
            <h2>Galeri Foto</h2>
            <ul>
                @foreach ($costume->images as $image)
                    <li>
                        <img src="{{ $image->url() }}" alt="Foto tambahan kostum {{ $costume->character_name }}" width="200" height="150">
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    <section>
        <h2>Deskripsi</h2>
        <p>{!! nl2br(e($costume->description)) !!}</p>

        <h3>Aksesori dan Kelengkapan</h3>
        <p>{{ $costume->accessories ?: 'Informasi aksesori belum diisi tenant.' }}</p>

        <h3>Aturan Sewa</h3>
        <ul>
            <li>Durasi sewa minimal {{ config('cosplayin.rental.min_days') }} hari dan maksimal {{ config('cosplayin.rental.max_days') }} hari (dapat berubah sesuai pengaturan admin).</li>
            <li>Penyewa wajib mengunggah foto KTP asli untuk verifikasi identitas.</li>
            <li>Kostum harus dikembalikan sesuai tanggal yang disepakati.</li>
            <li>Keterlambatan dikenakan denda per hari sesuai pengaturan platform.</li>
            <li>Kerusakan atau kehilangan diklaim dari deposit.</li>
            <li>{{ config('cosplayin.cancellation.policy_text') }}</li>
        </ul>
    </section>

    <section>
        <h2>Harga dan Ukuran</h2>
        <p>Harga sewa: <strong>{{ rupiah($costume->price_per_day) }} / hari</strong></p>
        <p>Deposit: {{ rupiah($costume->deposit > 0 ? $costume->deposit : config('cosplayin.deposit')) }}</p>

        <table>
            <caption>Stok per ukuran</caption>
            <thead>
                <tr>
                    <th scope="col">Ukuran</th>
                    <th scope="col">Stok</th>
                </tr>
            </thead>
            <tbody>
                @foreach (App\Models\Costume::SIZES as $size)
                    @php($stock = $costume->sizes->firstWhere('size', $size))
                    <tr>
                        <td>{{ $size }}</td>
                        <td>{{ $stock?->stock ?? 0 }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>

    <section>
        <h2>Cek Ketersediaan Tanggal</h2>

        @if ($availability)
            <p role="status">
                Ukuran {{ $availability['ukuran'] }} untuk {{ $availability['tanggal_mulai'] }} sampai {{ $availability['tanggal_selesai'] }}:
                @if ($availability['available'] > 0)
                    tersedia ({{ $availability['available_stock'] }} unit).
                @else
                    tidak tersedia.
                @endif
            </p>
        @endif

        <form method="GET" action="{{ route('catalog.availability', $costume) }}">
            <p>
                <label for="cek_ukuran">Ukuran</label>
                <select id="cek_ukuran" name="ukuran" required>
                    @foreach (App\Models\Costume::SIZES as $size)
                        <option value="{{ $size }}">{{ $size }}</option>
                    @endforeach
                </select>
            </p>
            <p>
                <label for="cek_tanggal_mulai">Tanggal mulai</label>
                <input id="cek_tanggal_mulai" name="tanggal_mulai" type="date" min="{{ now()->toDateString() }}" required>
            </p>
            <p>
                <label for="cek_tanggal_selesai">Tanggal kembali</label>
                <input id="cek_tanggal_selesai" name="tanggal_selesai" type="date" min="{{ now()->addDay()->toDateString() }}" required>
            </p>
            <p><button type="submit">Cek Ketersediaan</button></p>
        </form>
    </section>

    <section>
        <h2>Sewa Kostum Ini</h2>
        <p>
            <a href="{{ route('booking.create', $costume) }}">Sewa Sekarang</a>
        </p>
        @guest
            <p>Anda akan diarahkan ke halaman login terlebih dahulu. Setelah login, Anda kembali ke halaman booking kostum ini.</p>
        @endguest
    </section>

    <section>
        <h2>Ulasan Customer</h2>

        @if ($reviews->isEmpty())
            <p>Belum ada ulasan untuk kostum ini.</p>
        @else
            <ul>
                @foreach ($reviews as $review)
                    <li>
                        <p><strong>{{ $review->reviewer?->name ?? 'Pengguna' }}</strong> - Rating {{ $review->rating }}/5</p>
                        <p>{{ $review->comment ?: 'Tanpa komentar.' }}</p>
                        <p><small>{{ $review->created_at->format('d/m/Y H:i') }}</small></p>
                    </li>
                @endforeach
            </ul>

            {{ $reviews->links() }}
        @endif
    </section>
</article>
@endsection
