@php
    $showLink = $showLink ?? true;
    $tall = $tall ?? false;
@endphp

<section class="dashboard-panel {{ $tall ? 'dashboard-panel-lg' : '' }}">
    <div class="dashboard-panel-header">
        <h2 class="dashboard-section-title mb-0">Riwayat penyewaan</h2>

        @if ($showLink)
            <a href="{{ route('customer.rentals.history') }}" class="dashboard-panel-link">
                Lihat semua &rarr;
            </a>
        @endif
    </div>

    <div class="dashboard-table-wrap">
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th scope="col">ID pesanan</th>
                    <th scope="col">Kostum</th>
                    <th scope="col">Tanggal sewa</th>
                    <th scope="col">Total</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($history as $booking)
                    <tr>
                        <td data-label="ID pesanan">#{{ $booking->booking_code }}</td>
                        <td data-label="Kostum">{{ $booking->costume?->character_name }}</td>
                        <td data-label="Tanggal sewa">{{ $booking->rentalPeriodShort() }}</td>
                        <td data-label="Total">{{ rupiah($booking->total) }}</td>
                        <td data-label="Status">{{ $booking->statusLabel() }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="dashboard-table-empty">Belum ada riwayat sewa.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
