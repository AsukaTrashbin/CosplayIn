@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
@include('partials.dashboard-nav')

<h1>Manajemen Pengguna</h1>

<form method="GET" action="{{ route('admin.users.index') }}">
    <p>
        <label for="role">Peran</label>
        <select id="role" name="role">
            <option value="">Semua peran</option>
            <option value="customer" @selected(($filters['role'] ?? '') === 'customer')>Customer</option>
            <option value="tenant" @selected(($filters['role'] ?? '') === 'tenant')>Tenant</option>
            <option value="admin" @selected(($filters['role'] ?? '') === 'admin')>Admin</option>
        </select>
    </p>
    <p>
        <label for="status">Status</label>
        <select id="status" name="status">
            <option value="">Semua status</option>
            <option value="active" @selected(($filters['status'] ?? '') === 'active')>Aktif</option>
            <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Nonaktif</option>
        </select>
    </p>
    <p>
        <label for="q">Cari nama, email, atau username</label>
        <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}">
    </p>
    <p><button type="submit">Filter</button></p>
</form>

@if ($users->isEmpty())
    <p>Tidak ada pengguna.</p>
@else
    <table>
        <caption>Daftar pengguna</caption>
        <thead>
            <tr>
                <th scope="col">Nama</th>
                <th scope="col">Username</th>
                <th scope="col">Email</th>
                <th scope="col">Peran</th>
                <th scope="col">Tenant</th>
                <th scope="col">Status</th>
                <th scope="col">Verifikasi email</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->username }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->roleLabel() }}</td>
                    <td>{{ $user->tenant?->store_name ?? '-' }}</td>
                    <td>{{ $user->status === 'active' ? 'Aktif' : 'Nonaktif' }}</td>
                    <td>{{ $user->email_verified_at ? 'Terverifikasi' : 'Belum terverifikasi' }}</td>
                    <td>
                        @if ($user->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.toggle', $user) }}">
                                @csrf
                                <button type="submit">{{ $user->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                            </form>
                        @else
                            Akun Anda
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $users->links() }}
@endif
@endsection
