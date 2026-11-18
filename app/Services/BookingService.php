<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Costume;
use App\Models\CostumeSize;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(private readonly AvailabilityService $availability)
    {
    }

    /**
     * Membuat booking dengan transaksi dan row lock untuk mencegah double booking.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(User $customer, Costume $costume, array $data): Booking
    {
        return DB::transaction(function () use ($customer, $costume, $data) {
            $size = CostumeSize::query()
                ->where('costume_id', $costume->id)
                ->where('size', $data['ukuran'])
                ->lockForUpdate()
                ->first();

            if (! $size || $size->stock < 1) {
                throw ValidationException::withMessages([
                    'ukuran' => 'Ukuran yang dipilih tidak tersedia.',
                ]);
            }

            $start = Carbon::parse($data['tanggal_mulai'])->startOfDay();
            $end = Carbon::parse($data['tanggal_selesai'])->startOfDay();

            $duration = $start->diffInDays($end);
            $min = (int) Setting::get('min_rental_days', config('cosplayin.rental.min_days', 1));
            $max = (int) Setting::get('max_rental_days', config('cosplayin.rental.max_days', 14));

            if ($duration < $min || $duration > $max) {
                throw ValidationException::withMessages([
                    'tanggal_selesai' => "Durasi sewa harus antara {$min} sampai {$max} hari.",
                ]);
            }

            $this->availability->assertAvailable($costume, $data['ukuran'], $start, $end);

            $rentalPrice = $costume->price_per_day * $duration;
            $deposit = $costume->deposit > 0
                ? (int) $costume->deposit
                : (int) Setting::get('default_deposit', config('cosplayin.deposit', 100000));
            $shippingFee = ($data['metode_pengiriman'] ?? null) === 'courier'
                ? (int) Setting::get('shipping_fee', config('cosplayin.shipping_fee', 25000))
                : 0;

            $booking = Booking::query()->create([
                'customer_id' => $customer->id,
                'tenant_id' => $costume->tenant_id,
                'costume_id' => $costume->id,
                'size' => $data['ukuran'],
                'booker_name' => $data['nama_penyewa'],
                'identity_number' => $data['nomor_identitas'],
                'phone' => $data['telepon'],
                'address' => $data['alamat'],
                'rental_date' => $start->toDateString(),
                'return_date' => $end->toDateString(),
                'duration_days' => $duration,
                'rental_price' => $rentalPrice,
                'deposit' => $deposit,
                'shipping_fee' => $shippingFee,
                'total' => $rentalPrice + $deposit + $shippingFee,
                'status' => Booking::STATUS_WAITING_CONFIRMATION,
                'shipping_method' => $data['metode_pengiriman'],
                'ktp_path' => $data['ktp_path'],
            ]);

            $this->availability->blockForBooking($booking);

            return $booking;
        });
    }

    public function tenantIsActive(Costume $costume): bool
    {
        return $costume->tenant && $costume->tenant->status === Tenant::STATUS_ACTIVE;
    }
}
