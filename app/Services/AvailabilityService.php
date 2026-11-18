<?php

namespace App\Services;

use App\Models\AvailabilityBlock;
use App\Models\Booking;
use App\Models\Costume;
use App\Models\CostumeSize;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class AvailabilityService
{
    /**
     * Daftar tanggal inklusif mulai, eksklusif tanggal kembali.
     *
     * @return array<int, string>
     */
    public function dates(CarbonInterface|string $start, CarbonInterface|string $end): array
    {
        $cursor = Carbon::parse($start)->startOfDay();
        $last = Carbon::parse($end)->startOfDay();
        $dates = [];

        while ($cursor->lt($last)) {
            $dates[] = $cursor->toDateString();
            $cursor->addDay();
        }

        return $dates;
    }

    public function blockedCount(int $costumeId, string $size, CarbonInterface|string $start, CarbonInterface|string $end): int
    {
        $dates = $this->dates($start, $end);

        if ($dates === []) {
            return 0;
        }

        return AvailabilityBlock::query()
            ->where('costume_id', $costumeId)
            ->where('size', $size)
            ->whereIn('date', $dates)
            ->count();
    }

    public function totalStock(int $costumeId, string $size): int
    {
        return (int) CostumeSize::query()
            ->where('costume_id', $costumeId)
            ->where('size', $size)
            ->value('stock');
    }

    public function availableStock(int $costumeId, string $size, CarbonInterface|string $start, CarbonInterface|string $end): int
    {
        $total = $this->totalStock($costumeId, $size);

        return max(0, $total - $this->blockedCount($costumeId, $size, $start, $end));
    }

    public function isAvailable(Costume $costume, string $size, CarbonInterface|string $start, CarbonInterface|string $end): bool
    {
        return $this->availableStock($costume->id, $size, $start, $end) > 0;
    }

    /**
     * @throws ValidationException
     */
    public function assertAvailable(Costume $costume, string $size, CarbonInterface|string $start, CarbonInterface|string $end): void
    {
        if ($this->availableStock($costume->id, $size, $start, $end) <= 0) {
            throw ValidationException::withMessages([
                'ukuran' => 'Ukuran '.$size.' tidak tersedia pada tanggal yang dipilih. Silakan pilih tanggal atau ukuran lain.',
            ]);
        }
    }

    public function blockForBooking(Booking $booking): void
    {
        $dates = $this->dates($booking->rental_date, $booking->return_date);

        foreach ($dates as $date) {
            AvailabilityBlock::query()->create([
                'costume_id' => $booking->costume_id,
                'size' => $booking->size,
                'date' => $date,
                'type' => AvailabilityBlock::TYPE_BOOKED,
                'booking_id' => $booking->id,
            ]);
        }
    }

    public function releaseForBooking(Booking $booking): void
    {
        AvailabilityBlock::query()->where('booking_id', $booking->id)->delete();
    }

    /**
     * Tanggal yang sudah terblokir untuk kombinasi kostum dan ukuran.
     *
     * @return array<int, string>
     */
    public function blockedDates(Costume $costume, string $size): array
    {
        return AvailabilityBlock::query()
            ->where('costume_id', $costume->id)
            ->where('size', $size)
            ->where('date', '>=', now()->startOfDay())
            ->orderBy('date')
            ->pluck('date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->all();
    }
}
