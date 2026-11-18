@extends('layouts.app')

@section('title', 'Kostum Saya')

@section('content')
@include('partials.dashboard-nav')

<h1>Kostum Saya</h1>

<p><a href="{{ route('tenant.costumes.create') }}">Tambah kostum baru</a></p>

@if ($costumes->isEmpty())
    <p>Belum ada kostum. Tambahkan kostum pertama Anda.</p>
@else
    <table>
        <caption>Daftar kostum milik toko Anda</caption>
        <thead>
            <tr>
                <th scope="col">Foto</th>
                <th scope="col">Nama karakter</th>
                <th scope="col">Kategori</th>
                <th scope="col">Gender</th>
                <th scope="col">Kota</th>
                <th scope="col">Harga / hari</th>
                <th scope="col">Stok S/M/L/XL</th>
                <th scope="col">Status</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($costumes as $costume)
                @php($sizes = $costume->sizes->keyBy('size'))
                <tr>
                    <td><img src="{{ $costume->primaryImageUrl() }}" alt="Foto {{ $costume->character_name }}" width="100" height="70"></td>
                    <td>{{ $costume->character_name }}</td>
                    <td>{{ $costume->category?->name ?? '-' }}</td>
                    <td>{{ $costume->genderLabel() }}</td>
                    <td>{{ $costume->city }}</td>
                    <td>{{ rupiah($costume->price_per_day) }}</td>
                    <td>
                        {{ $sizes->get('S')?->stock ?? 0 }} /
                        {{ $sizes->get('M')?->stock ?? 0 }} /
                        {{ $sizes->get('L')?->stock ?? 0 }} /
                        {{ $sizes->get('XL')?->stock ?? 0 }}
                    </td>
                    <td>{{ $costume->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                    <td>
                        <a href="{{ route('catalog.show', $costume) }}">Lihat</a>
                        <a href="{{ route('tenant.costumes.edit', $costume) }}">Edit</a>
                        <a href="{{ route('tenant.costumes.schedule', $costume) }}">Jadwal</a>
                        <form method="POST" action="{{ route('tenant.costumes.destroy', $costume) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $costumes->links() }}
@endif
@endsection
