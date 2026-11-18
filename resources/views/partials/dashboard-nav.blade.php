@auth
<nav aria-label="Menu dashboard">
    <p><strong>Menu {{ auth()->user()->roleLabel() }}</strong></p>
    <ul>
        <li><a href="{{ route(auth()->user()->dashboardRoute()) }}">Dashboard</a></li>

        @if (auth()->user()->isCustomer())
            <li><a href="{{ route('dashboard') }}#pesanan-aktif">Pesanan Aktif</a></li>
            <li><a href="{{ route('dashboard') }}#status-pembayaran">Status Pembayaran</a></li>
            <li><a href="{{ route('dashboard') }}#riwayat">Riwayat Sewa</a></li>
        @endif

        @if (auth()->user()->isTenant())
            <li><a href="{{ route('tenant.bookings.index') }}">Pengajuan Sewa</a></li>
            <li><a href="{{ route('tenant.orders.index') }}">Pesanan Aktif</a></li>
            <li><a href="{{ route('tenant.orders.index', ['status' => 'completed']) }}">Riwayat Pesanan</a></li>
            <li><a href="{{ route('tenant.costumes.index') }}">Kostum Saya</a></li>
            <li><a href="{{ route('tenant.categories.index') }}">Kategori</a></li>
            <li><a href="{{ route('tenant.reports') }}">Laporan Pendapatan</a></li>
            <li><a href="{{ route('tenant.profile.edit') }}">Profil Toko</a></li>
        @endif

        @if (auth()->user()->isAdmin())
            <li><a href="{{ route('admin.tenants.index') }}">Manajemen Tenant</a></li>
            <li><a href="{{ route('admin.transactions.index') }}">Transaksi</a></li>
            <li><a href="{{ route('admin.users.index') }}">Pengguna</a></li>
            <li><a href="{{ route('admin.settings.edit') }}">Pengaturan</a></li>
        @endif

        <li><a href="{{ route('notifications.index') }}">Notifikasi</a></li>
        <li><a href="{{ route('catalog.index') }}">Katalog Publik</a></li>
    </ul>
</nav>
@endauth
