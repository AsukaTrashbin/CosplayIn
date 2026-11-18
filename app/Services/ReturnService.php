<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\ReturnItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReturnService
{
    public function __construct(
        private readonly LateFeeService $lateFee,
        private readonly AvailabilityService $availability,
        private readonly NotificationService $notifications,
    ) {
    }

    /**
     * Customer memulai proses pengembalian.
     *
     * @param  array<string, mixed>  $data
     */
    public function start(Booking $booking, array $data): ReturnItem
    {
        $return = DB::transaction(function () use ($booking, $data) {
            $locked = Booking::query()->lockForUpdate()->findOrFail($booking->id);

            if (! in_array($locked->status, [
                Booking::STATUS_PAID,
                Booking::STATUS_READY_TO_PROCESS,
                Booking::STATUS_RENTED,
            ], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Pesanan ini tidak dapat diproses untuk pengembalian.',
                ]);
            }

            if ($locked->return_date->gt(now()->startOfDay())) {
                throw ValidationException::withMessages([
                    'status' => 'Proses pengembalian baru dapat dimulai setelah masa sewa berakhir.',
                ]);
            }

            if ($locked->returnItem()->exists()) {
                throw ValidationException::withMessages([
                    'status' => 'Pengembalian untuk pesanan ini sudah diajukan.',
                ]);
            }

            $return = ReturnItem::query()->create([
                'booking_id' => $locked->id,
                'method' => $data['metode'],
                'tracking_number' => $data['nomor_resi'] ?? null,
                'cod_location' => $data['lokasi_cod'] ?? null,
                'cod_time' => $data['waktu_cod'] ?? null,
            ]);

            $locked->update(['status' => Booking::STATUS_RETURNED]);

            return $return->fresh(['booking.customer', 'booking.tenant.user']);
        });

        $this->notifications->send(
            $return->booking->tenant->user,
            'Pengembalian Kostum Diajukan',
            "Customer mengajukan pengembalian untuk pesanan {$return->booking->booking_code}. Silakan verifikasi kondisi kostum.",
            route('tenant.orders.show', $return->booking)
        );

        return $return;
    }

    /**
     * Tenant memverifikasi kondisi kostum dan menghitung denda/deposit.
     *
     * @param  array<string, mixed>  $data
     */
    public function verify(ReturnItem $return, array $data): ReturnItem
    {
        $return = DB::transaction(function () use ($return, $data) {
            $lockedReturn = ReturnItem::query()->lockForUpdate()->findOrFail($return->id);
            $booking = Booking::query()->lockForUpdate()->findOrFail($lockedReturn->booking_id);

            if ($booking->status !== Booking::STATUS_RETURNED) {
                throw ValidationException::withMessages([
                    'status' => 'Pesanan tidak berada pada status menunggu verifikasi pengembalian.',
                ]);
            }

            $late = $this->lateFee->calculate($booking, $data['tanggal_kembali_aktual']);
            $condition = $data['kondisi'];
            $damageClaim = $condition === ReturnItem::CONDITION_DAMAGED_LOST
                ? (int) ($data['klaim_kerusakan'] ?? 0)
                : 0;

            // Deposit hanya dipotong untuk klaim kerusakan/kehilangan.
            // Denda keterlambatan ditagih terpisah (TODO: mekanisme penagihan final).
            $depositDeduction = min((int) $booking->deposit, $damageClaim);
            $depositRefund = max(0, (int) $booking->deposit - $depositDeduction);

            $lockedReturn->update([
                'actual_return_date' => $data['tanggal_kembali_aktual'],
                'condition' => $condition,
                'days_late' => $late['days_late'],
                'late_fee' => $late['late_fee'],
                'damage_claim' => $damageClaim,
                'deposit_deduction' => $depositDeduction,
                'deposit_refunded' => $depositRefund,
                'tenant_note' => $data['catatan_tenant'] ?? null,
            ]);

            $booking->update(['status' => Booking::STATUS_COMPLETED]);
            $this->availability->releaseForBooking($booking);

            return $lockedReturn->fresh(['booking.customer', 'booking.tenant.user']);
        });

        $booking = $return->booking;

        $this->notifications->send(
            $booking->customer,
            'Pengembalian Selesai',
            "Pengembalian pesanan {$booking->booking_code} telah diverifikasi. Denda keterlambatan: Rp".number_format($return->late_fee, 0, ',', '.').'. Deposit dikembalikan: Rp'.number_format($return->deposit_refunded, 0, ',', '.').'.',
            route('orders.show', $booking)
        );

        $this->notifications->send(
            $booking->tenant->user,
            'Pengembalian Diverifikasi',
            "Pengembalian pesanan {$booking->booking_code} selesai. Pesanan telah ditandai selesai.",
            route('tenant.orders.show', $booking)
        );

        return $return;
    }
}
