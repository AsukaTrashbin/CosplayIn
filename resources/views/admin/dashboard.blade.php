@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
@include('partials.dashboard-nav')

<h1>Dashboard Admin</h1>

<section>
    <h2>Ringkasan Platform</h2>
    <dl>
        <dt>Total pengguna terdaftar</dt>
        <dd>{{ $totalUsers }}</dd>
        <dt>Total kostum</dt>
        <dd>{{ $totalCostumes }}</dd>
        <dt>Total tenant aktif</dt>
        <dd>{{ $totalTenants }}</dd>
        <dt>Total pendapatan (pembayaran sukses)</dt>
        <dd>{{ rupiah($revenue) }}</dd>
        <dt>Pendapatan platform (komisi {{ $commissionPercent }}%)</dt>
        <dd>{{ rupiah($earnings) }}</dd>
        <dt>Rating rata-rata platform</dt>
        <dd>{{ $averageRating ? round((float) $averageRating, 2).' / 5' : 'Belum ada rating' }}</dd>
    </dl>
</section>

<section>
    <h2>Tenant Menunggu Persetujuan</h2>

    @if ($pendingTenants->isEmpty())
        <p>Tidak ada tenant yang menunggu persetujuan.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th scope="col">Nama toko</th>
                    <th scope="col">Pemilik</th>
                    <th scope="col">Kota</th>
                    <th scope="col">Tanggal daftar</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pendingTenants as $tenant)
                    <tr>
                        <td>{{ $tenant->store_name }}</td>
                        <td>{{ $tenant->user->name }} ({{ $tenant->user->email }})</td>
                        <td>{{ $tenant->city }}</td>
                        <td>{{ $tenant->created_at->format('d/m/Y') }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.tenants.approve', $tenant) }}">
                                @csrf
                                <button type="submit">Setujui</button>
                            </form>
                            <form method="POST" action="{{ route('admin.tenants.deactivate', $tenant) }}">
                                @csrf
                                <button type="submit">Tolak / Nonaktifkan</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</section>

<section>
    <h2>Transaksi per Status</h2>
    @if ($bookingsByStatus->isEmpty())
        <p>Belum ada transaksi.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th scope="col">Status</th>
                    <th scope="col">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bookingsByStatus as $status => $total)
                    <tr>
                        <td>{{ $status }}</td>
                        <td>{{ $total }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</section>

<section>
    <h2>Pendapatan per Bulan</h2>
    @if ($monthlyRevenue->isEmpty())
        <p>Belum ada data pendapatan.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th scope="col">Bulan</th>
                    <th scope="col">Jumlah pesanan</th>
                    <th scope="col">Nilai transaksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($monthlyRevenue as $row)
                    <tr>
                        <td>{{ $row['month'] }}</td>
                        <td>{{ $row['orders'] }}</td>
                        <td>{{ rupiah($row['total']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</section>

<section>
    <h2>Transaksi Terbaru</h2>
    @if ($recentBookings->isEmpty())
        <p>Belum ada transaksi.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th scope="col">Kode</th>
                    <th scope="col">Customer</th>
                    <th scope="col">Tenant</th>
                    <th scope="col">Kostum</th>
                    <th scope="col">Status</th>
                    <th scope="col">Total</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($recentBookings as $booking)
                    <tr>
                        <td>{{ $booking->booking_code }}</td>
                        <td>{{ $booking->customer->name }}</td>
                        <td>{{ $booking->tenant->store_name }}</td>
                        <td>{{ $booking->costume->character_name }}</td>
                        <td>{{ $booking->statusLabel() }}</td>
                        <td>{{ rupiah($booking->total) }}</td>
                        <td><a href="{{ route('admin.transactions.show', $booking) }}">Detail</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</section>
@endsection
