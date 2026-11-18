@extends('layouts.app')

@section('title', 'Kategori Kostum')

@section('content')
@include('partials.dashboard-nav')

<h1>Kategori Kostum</h1>

<p><a href="{{ route('tenant.categories.create') }}">Tambah kategori baru</a></p>

<p>Kategori global (tanpa label tenant) hanya dapat diubah oleh admin. Kategori buatan tenant hanya dapat diubah oleh tenant tersebut.</p>

@if ($categories->isEmpty())
    <p>Belum ada kategori.</p>
@else
    <table>
        <caption>Kategori global dan kategori milik toko Anda</caption>
        <thead>
            <tr>
                <th scope="col">Nama</th>
                <th scope="col">Jenis</th>
                <th scope="col">Jumlah kostum</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($categories as $category)
                <tr>
                    <td>{{ $category->name }}</td>
                    <td>{{ $category->tenant_id === null ? 'Global' : 'Milik toko' }}</td>
                    <td>{{ $category->costumes_count }}</td>
                    <td>
                        @if ($category->tenant_id === auth()->user()->tenant->id)
                            <a href="{{ route('tenant.categories.edit', $category) }}">Edit</a>
                            <form method="POST" action="{{ route('tenant.categories.destroy', $category) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit">Hapus</button>
                            </form>
                        @else
                            Tidak dapat diubah
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $categories->links() }}
@endif
@endsection
