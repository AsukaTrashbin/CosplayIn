<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'method' => 'transfer',
            'external_id' => 'MOCK-'.strtoupper(Str::random(12)),
            'amount' => 575000,
            'status' => Payment::STATUS_PENDING,
            'paid_at' => null,
        ];
    }

    public function success(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Payment::STATUS_SUCCESS,
            'paid_at' => now(),
        ]);
    }
}
