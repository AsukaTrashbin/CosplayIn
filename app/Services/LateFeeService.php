<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Setting;
use Illuminate\Support\Carbon;

class LateFeeService
{
    /**
     * Hitung denda keterlambatan.
     *
     * TODO: Formula final belum ditentukan. Nilai tarif dan batas maksimum
     * dibaca dari tabel settings agar dapat diubah admin.
     *
     * @return array{days_late:int, late_fee:int, rate:int, maximum:int}
     */
    public function calculate(Booking $booking, Carbon|string $actualReturnDate): array
    {
        $expected = Carbon::parse($booking->return_date)->startOfDay();
        $actual = Carbon::parse($actualReturnDate)->startOfDay();

        $daysLate = $actual->gt($expected) ? (int) $expected->diffInDays($actual) : 0;

        $rate = (int) Setting::get('late_fee_per_day', config('cosplayin.late_fee.per_day', 50000));
        $maximum = (int) Setting::get('max_late_fee', config('cosplayin.late_fee.max', 500000));

        $fee = min($daysLate * $rate, $maximum);

        return [
            'days_late' => $daysLate,
            'late_fee' => max(0, $fee),
            'rate' => $rate,
            'maximum' => $maximum,
        ];
    }
}
