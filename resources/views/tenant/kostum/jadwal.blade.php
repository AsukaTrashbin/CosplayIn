@extends('layouts.app')

@section('title', 'Jadwal Kostum '.$costume->character_name)

@section('content')
@include('partials.dashboard-nav')

<h1>Jadwal Ketersediaan: {{ $costume->character_name }}</h1>

<p><a href="{{ route('tenant.costumes.index') }}">&laquo; Kembali ke daftar kostum</a></p>

<section>
    <h2>Stok per Ukuran</h2>
    <table>
        <thead>
            <tr>
                <th scope="col">Ukuran</th>
                <th scope="col">Stok total</th>
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
    <h2>Blokir Tanggal untuk Maintenance</h2>

    <p>Blokir ini mengurangi ketersediaan unit pada tanggal dan ukuran yang dipilih. Booking yang sudah ada tidak dapat diubah manual dari halaman ini.</p>

    <form method="POST" action="{{ route('tenant.costumes.schedule.store', $costume) }}">
        @csrf

        <p>
            <label for="ukuran">Ukuran</label>
            <select id="ukuran" name="ukuran" required>
                @foreach (App\Models\Costume::SIZES as $size)
                    <option value="{{ $size }}">{{ $size }}</option>
                @endforeach
            </select>
        </p>

        <p>
            <label for="tanggal">Tanggal</label>
            <input id="tanggal" name="tanggal" type="date" min="{{ now()->toDateString() }}" required>
        </p>

        <p>
            <label for="catatan">Catatan (opsional)</label>
            <input id="catatan" name="catatan" type="text" value="{{ old('catatan') }}">
        </p>

        <p><button type="submit">Blokir Tanggal</button></p>
    </form>
</section>

<section>
    <h2>Jadwal Terblokir (Mulai Hari Ini)</h2>

    @if ($blocks->isEmpty())
        <p>Tidak ada tanggal terblokir.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th scope="col">Tanggal</th>
                    <th scope="col">Ukuran</th>
                    <th scope="col">Tipe</th>
                    <th scope="col">Catatan</th>
                    <th scope="col">Kode Booking</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($blocks as $block)
                    <tr>
                        <td>{{ tanggal_indonesia($block->date) }}</td>
                        <td>{{ $block->size }}</td>
                        <td>{{ $block->type === App\Models\AvailabilityBlock::TYPE_MAINTENANCE ? 'Maintenance' : 'Booking' }}</td>
                        <td>{{ $block->notes ?? '-' }}</td>
                        <td>{{ $block->booking?->booking_code ?? '-' }}</td>
                        <td>
                            @if ($block->type === App\Models\AvailabilityBlock::TYPE_MAINTENANCE)
                                <form method="POST" action="{{ route('tenant.costumes.schedule.destroy', [$costume, $block]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">Hapus Blokir</button>
                                </form>
                            @else
                                Tidak dapat dihapus manual
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</section>
@endsection
