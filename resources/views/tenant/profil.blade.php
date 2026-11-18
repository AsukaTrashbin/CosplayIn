@extends('layouts.app')

@section('title', 'Profil Toko')

@section('content')
@include('partials.dashboard-nav')

<h1>Profil Toko</h1>
<p>Status tenant: {{ $tenant->statusLabel() }}</p>

<form method="POST" action="{{ route('tenant.profile.update') }}">
    @csrf
    @method('PUT')

    <p>
        <label for="store_name">Nama toko</label>
        <input id="store_name" name="store_name" type="text" value="{{ old('store_name', $tenant->store_name) }}" required>
    </p>

    <p>
        <label for="city">Kota</label>
        <input id="city" name="city" type="text" value="{{ old('city', $tenant->city) }}" required>
    </p>

    <p>
        <label for="address">Alamat</label>
        <textarea id="address" name="address" rows="3" required>{{ old('address', $tenant->address) }}</textarea>
    </p>

    <p>
        <label for="phone">Telepon</label>
        <input id="phone" name="phone" type="text" value="{{ old('phone', $tenant->phone) }}">
    </p>

    <p>
        <label for="legal_name">Nama legal</label>
        <input id="legal_name" name="legal_name" type="text" value="{{ old('legal_name', $tenant->legal_name) }}" required>
    </p>

    <p>
        <label for="legal_id_number">Nomor identitas legal</label>
        <input id="legal_id_number" name="legal_id_number" type="text" value="{{ old('legal_id_number', $tenant->legal_id_number) }}" required>
    </p>

    <p><button type="submit">Simpan Profil</button></p>
</form>
@endsection
