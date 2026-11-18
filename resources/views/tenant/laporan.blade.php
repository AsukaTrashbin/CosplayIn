@extends('layouts.app')

@section('title', 'Laporan Pendapatan')

@section('content')
@include('partials.dashboard-nav')

<h1>Laporan Pendapatan Tenant</h1>

<section>
    <h2>Ringkasan</h2>
    <dl>
        <dt>Total transaksi selesai</dt>
        <dd>{{ $completedCount }}</dd>
        <dt>Pendapatan kotor</dt>
        <dd>{{ rupiah($gross) }}</dd>
        <dt>Komisi platform ({{ $commissionPercent }}%)</dt>
        <dd>{{ rupiah($commission) }}</dd>
        <dt>Pendapatan bersih</dt>
        <dd><strong>{{ rupiah($earnings) }}</strong></dd>
    </dl>
    <p>Perhitungan komisi bersifat sementara dan dapat berubah sesuai kebijakan platform.</p>
</section>

<section>
    <h2>Pendapatan per Bulan</h2>

    @if ($monthly->isEmpty())
        <p>Belum ada transaksi selesai.</p>
    @else
        <table>
            <caption>Rekap pendapatan bulanan</caption>
            <thead>
                <tr>
                    <th scope="col">Bulan</th>
                    <th scope="col">Jumlah pesanan</th>
                    <th scope="col">Pendapatan kotor</th>
                    <th scope="col">Total denda keterlambatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($monthly as $row)
                    <tr>
                        <td>{{ $row['month'] }}</td>
                        <td>{{ $row['orders'] }}</td>
                        <td>{{ rupiah($row['gross']) }}</td>
                        <td>{{ rupiah($row['late_fees']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</section>

<section>
    <h2>Transaksi Selesai Terakhir</h2>

    @if ($completed->isEmpty())
        <p>Belum ada transaksi selesai.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th scope="col">Kode</th>
                    <th scope="col">Kostum</th>
                    <th scope="col">Total</th>
                    <th scope="col">Tanggal selesai</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($completed as $booking)
                    <tr>
                        <td><a href="{{ route('tenant.orders.show', $booking) }}">{{ $booking->booking_code }}</a></td>
                        <td>{{ $booking->costume->character_name }}</td>
                        <td>{{ rupiah($booking->total) }}</td>
                        <td>{{ $booking->updated_at->format('d/m/Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</section>
@endsection
