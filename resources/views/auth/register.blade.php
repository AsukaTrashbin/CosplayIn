@extends('layouts.app')

@section('title', 'Daftar')

@section('content')
<h1>Daftar Akun CosplayIn</h1>

<p>Sudah punya akun? <a href="{{ route('login') }}">Login di sini</a>.</p>

<form method="POST" action="{{ route('register.store') }}">
    @csrf

    <fieldset>
        <legend>Data Akun</legend>

        <p>
            <label for="role">Daftar sebagai</label>
            <select id="role" name="role" required>
                <option value="customer" @selected(old('role', 'customer') === 'customer')>Customer (Penyewa)</option>
                <option value="tenant" @selected(old('role') === 'tenant')>Tenant (Penyedia Kostum)</option>
            </select>
        </p>

        <p>
            <label for="name">Nama lengkap</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autocomplete="name">
        </p>

        <p>
            <label for="username">Username</label>
            <input id="username" name="username" type="text" value="{{ old('username') }}" required autocomplete="username">
        </p>

        <p>
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">
        </p>

        <p>
            <label for="password">Password (minimal 8 karakter)</label>
            <input id="password" name="password" type="password" required autocomplete="new-password">
        </p>

        <p>
            <label for="password_confirmation">Konfirmasi password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
        </p>
    </fieldset>

    <fieldset>
        <legend>Data Tenant (wajib diisi jika mendaftar sebagai tenant)</legend>

        <p>
            <label for="store_name">Nama toko</label>
            <input id="store_name" name="store_name" type="text" value="{{ old('store_name') }}">
        </p>

        <p>
            <label for="city">Kota</label>
            <input id="city" name="city" type="text" value="{{ old('city') }}">
        </p>

        <p>
            <label for="address">Alamat toko</label>
            <textarea id="address" name="address" rows="3">{{ old('address') }}</textarea>
        </p>

        <p>
            <label for="phone">Nomor telepon</label>
            <input id="phone" name="phone" type="text" value="{{ old('phone') }}">
        </p>

        <p>
            <label for="legal_name">Nama legal pemilik</label>
            <input id="legal_name" name="legal_name" type="text" value="{{ old('legal_name') }}">
        </p>

        <p>
            <label for="legal_id_number">Nomor identitas legal (KTP/NIB)</label>
            <input id="legal_id_number" name="legal_id_number" type="text" value="{{ old('legal_id_number') }}">
        </p>

        <p>Tenant baru akan berstatus pending sampai disetujui admin.</p>
    </fieldset>

    <fieldset>
        <legend>Persetujuan</legend>

        <p>
            <input id="terms_accepted" name="terms_accepted" type="checkbox" value="1" @checked(old('terms_accepted')) required>
            <label for="terms_accepted">Saya menyetujui <a href="{{ route('pages.terms') }}" target="_blank">Syarat dan Ketentuan</a>.</label>
        </p>

        <p>
            <input id="privacy_accepted" name="privacy_accepted" type="checkbox" value="1" @checked(old('privacy_accepted')) required>
            <label for="privacy_accepted">Saya menyetujui <a href="{{ route('pages.privacy') }}" target="_blank">Kebijakan Privasi</a>.</label>
        </p>
    </fieldset>

    <p><button type="submit">Daftar</button></p>
</form>
@endsection
