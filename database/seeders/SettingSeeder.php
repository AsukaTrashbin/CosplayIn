<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // TODO: Nilai final belum ditentukan.
            'late_fee_per_day' => config('cosplayin.late_fee.per_day', 50000),
            'max_late_fee' => config('cosplayin.late_fee.max', 500000),
            'default_deposit' => config('cosplayin.deposit', 100000),
            'shipping_fee' => config('cosplayin.shipping_fee', 25000),
            'min_rental_days' => config('cosplayin.rental.min_days', 1),
            'max_rental_days' => config('cosplayin.rental.max_days', 14),
            'platform_commission_percent' => config('cosplayin.commission_percent', 10),
        ];

        foreach ($defaults as $key => $value) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => (string) $value]
            );
        }
    }
}
