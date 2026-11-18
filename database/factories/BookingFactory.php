<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Costume;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    public function definition(): array
    {
        $start = now()->addDay()->startOfDay();
        $end = now()->addDays(4)->startOfDay();
        $price = 150000;
        $duration = 3;

        return [
            'customer_id' => User::factory()->customer(),
            'tenant_id' => Tenant::factory(),
            'costume_id' => fn (array $attributes) => Costume::factory()->create([
                'tenant_id' => $attributes['tenant_id'],
            ])->id,
            'size' => 'M',
            'booker_name' => fake()->name(),
            'identity_number' => (string) fake()->numerify('################'),
            'phone' => '081234567890',
            'address' => fake()->address(),
            'rental_date' => $start->toDateString(),
            'return_date' => $end->toDateString(),
            'duration_days' => $duration,
            'rental_price' => $price * $duration,
            'deposit' => 100000,
            'shipping_fee' => 25000,
            'total' => ($price * $duration) + 100000 + 25000,
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'shipping_method' => 'courier',
            'ktp_path' => 'ktp/dummy-ktp.jpg',
        ];
    }

    public function waitingConfirmation(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Booking::STATUS_WAITING_CONFIRMATION,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Booking::STATUS_PAID,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Booking::STATUS_COMPLETED,
            'rental_date' => now()->subDays(5)->toDateString(),
            'return_date' => now()->subDays(2)->toDateString(),
        ]);
    }
}
