@extends('layouts.app')

@section('title', 'Manajemen Tenant')

@section('content')
@include('partials.dashboard-nav')

<h1>Manajemen Tenant</h1>

<form method="GET" action="{{ route('admin.tenants.index') }}">
    <p>
        <label for="status">Status</label>
        <select id="status" name="status">
            <option value="">Semua status</option>
            <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Pending</option>
            <option value="active" @selected(($filters['status'] ?? '') === 'active')>Aktif</option>
            <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Nonaktif</option>
        </select>
    </p>
    <p>
        <label for="q">Cari nama toko, kota, atau email</label>
        <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}">
    </p>
    <p><button type="submit">Filter</button></p>
</form>

@if ($tenants->isEmpty())
    <p>Tidak ada tenant.</p>
@else
    <table>
        <caption>Daftar tenant</caption>
        <thead>
            <tr>
                <th scope="col">Nama toko</th>
                <th scope="col">Pemilik</th>
                <th scope="col">Kota</th>
                <th scope="col">Data legal</th>
                <th scope="col">Kostum</th>
                <th scope="col">Pesanan</th>
                <th scope="col">Status</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tenants as $tenant)
                <tr>
                    <td>{{ $tenant->store_name }}</td>
                    <td>{{ $tenant->user->name }}<br><small>{{ $tenant->user->email }}</small></td>
                    <td>{{ $tenant->city }}</td>
                    <td>{{ $tenant->legal_name }}<br><small>{{ $tenant->legal_id_number }}</small></td>
                    <td>{{ $tenant->costumes_count }}</td>
                    <td>{{ $tenant->bookings_count }}</td>
                    <td>{{ $tenant->statusLabel() }}</td>
                    <td>
                        @if ($tenant->status === App\Models\Tenant::STATUS_PENDING)
                            <form method="POST" action="{{ route('admin.tenants.approve', $tenant) }}">
                                @csrf
                                <button type="submit">Setujui</button>
                            </form>
                        @endif

                        @if ($tenant->status === App\Models\Tenant::STATUS_ACTIVE)
                            <form method="POST" action="{{ route('admin.tenants.deactivate', $tenant) }}">
                                @csrf
                                <button type="submit">Nonaktifkan</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.tenants.activate', $tenant) }}">
                                @csrf
                                <button type="submit">Aktifkan</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $tenants->links() }}
@endif
@endsection
