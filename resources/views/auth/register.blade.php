@extends('layouts.app')

@section('title', 'Daftar')

@section('content')
<div class="ci-container py-5 my-lg-4">
    <div class="row align-items-start g-5">

        {{-- Panel kiri: ilustrasi --}}
        <div class="col-lg-6 d-none d-lg-block">
            <div class="auth-visual">
                <div class="auth-visual-inner"></div>
            </div>
        </div>

        {{-- Form kanan --}}
        <div class="col-lg-5 offset-lg-1">
            <div class="auth-form">

                {{-- Switch Login / Buat akun --}}
                <div class="auth-switch mb-4">
                    <a href="{{ route('login') }}">Login</a>
                    <a href="{{ route('register') }}" class="active">Buat akun</a>
                </div>

                <h1 class="section-title mb-3">Buat akun baru</h1>
                <p class="text-muted-ci mb-4">Daftar untuk mulai menyewa atau menyediakan kostum</p>

                <form method="POST" action="{{ route('register.store') }}" novalidate>
                    @csrf

                    {{-- Daftar sebagai --}}
                    <div class="mb-3">
                        <label for="role" class="form-label auth-label">Daftar sebagai</label>
                        <select id="role" name="role" class="form-select auth-input" required>
                            <option value="customer" @selected(old('role', 'customer') === 'customer')>Customer (Penyewa)</option>
                            <option value="tenant" @selected(old('role') === 'tenant')>Tenant (Penyedia Kostum)</option>
                        </select>
                    </div>

                    {{-- Nama lengkap --}}
                    <div class="mb-3">
                        <label for="name" class="form-label auth-label">Nama lengkap</label>
                        <input id="name" name="name" type="text"
                            class="form-control auth-input @error('name') is-invalid @enderror"
                            placeholder="Masukkan nama lengkap"
                            value="{{ old('name') }}" required autocomplete="name">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Username --}}
                    <div class="mb-3">
                        <label for="username" class="form-label auth-label">Username</label>
                        <input id="username" name="username" type="text"
                            class="form-control auth-input @error('username') is-invalid @enderror"
                            placeholder="Masukkan username"
                            value="{{ old('username') }}" required autocomplete="username">
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="mb-3">
                        <label for="email" class="form-label auth-label">Email</label>
                        <input id="email" name="email" type="email"
                            class="form-control auth-input @error('email') is-invalid @enderror"
                            placeholder="Masukkan email"
                            value="{{ old('email') }}" required autocomplete="email">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="mb-3">
                        <label for="password" class="form-label auth-label">Password <span class="text-muted-ci small">(minimal 8 karakter)</span></label>
                        <input id="password" name="password" type="password"
                            class="form-control auth-input @error('password') is-invalid @enderror"
                            placeholder="Masukkan password"
                            required autocomplete="new-password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Konfirmasi password --}}
                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label auth-label">Konfirmasi password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password"
                            class="form-control auth-input"
                            placeholder="Ulangi password"
                            required autocomplete="new-password">
                    </div>

                    {{-- Data tenant --}}
                    <div id="tenant-fields" class="d-none">
                        <hr class="sidebar-divider my-4">

                        <div class="auth-section-title">Data Tenant</div>
                        <p class="auth-note mb-3">Tenant baru akan berstatus pending sampai disetujui admin.</p>

                        <div class="mb-3">
                            <label for="store_name" class="form-label auth-label">Nama toko</label>
                            <input id="store_name" name="store_name" type="text"
                                class="form-control auth-input" value="{{ old('store_name') }}">
                        </div>

                        <div class="mb-3">
                            <label for="city" class="form-label auth-label">Kota</label>
                            <input id="city" name="city" type="text"
                                class="form-control auth-input" value="{{ old('city') }}">
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label auth-label">Alamat toko</label>
                            <textarea id="address" name="address" rows="3"
                                class="form-control auth-input">{{ old('address') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label auth-label">Nomor telepon</label>
                            <input id="phone" name="phone" type="text"
                                class="form-control auth-input" value="{{ old('phone') }}">
                        </div>

                        <div class="mb-3">
                            <label for="legal_name" class="form-label auth-label">Nama legal pemilik</label>
                            <input id="legal_name" name="legal_name" type="text"
                                class="form-control auth-input" value="{{ old('legal_name') }}">
                        </div>

                        <div class="mb-3">
                            <label for="legal_id_number" class="form-label auth-label">Nomor identitas legal (KTP/NIB)</label>
                            <input id="legal_id_number" name="legal_id_number" type="text"
                                class="form-control auth-input" value="{{ old('legal_id_number') }}">
                        </div>
                    </div>

                    {{-- Persetujuan --}}
                    <div class="mb-4">
                        <div class="form-check mb-2">
                            <input id="terms_accepted" name="terms_accepted" type="checkbox" value="1"
                                class="form-check-input catalog-checkbox" @checked(old('terms_accepted')) required>
                            <label for="terms_accepted" class="form-check-label auth-link">
                                Saya menyetujui <a href="{{ route('pages.terms') }}" target="_blank" class="auth-link fw-semibold">Syarat dan Ketentuan</a>.
                            </label>
                        </div>
                        <div class="form-check">
                            <input id="privacy_accepted" name="privacy_accepted" type="checkbox" value="1"
                                class="form-check-input catalog-checkbox" @checked(old('privacy_accepted')) required>
                            <label for="privacy_accepted" class="form-check-label auth-link">
                                Saya menyetujui <a href="{{ route('pages.privacy') }}" target="_blank" class="auth-link fw-semibold">Kebijakan Privasi</a>.
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 auth-submit">Daftar</button>
                </form>

                <p class="text-center text-muted-ci mt-4 mb-0">
                    Sudah punya akun?
                    <a href="{{ route('login') }}" class="auth-link fw-semibold ms-1">Login di sini</a>
                </p>
            </div>
        </div>

    </div>
</div>

<script>
    // Tampilkan bagian tenant hanya saat role "tenant" dipilih.
    (function () {
        const role = document.getElementById('role');
        const tenantFields = document.getElementById('tenant-fields');

        function toggleTenant() {
            tenantFields.classList.toggle('d-none', role.value !== 'tenant');
        }

        role.addEventListener('change', toggleTenant);
        toggleTenant();
    })();
</script>
@endsection