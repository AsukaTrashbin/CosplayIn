<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->tenant(),
            'store_name' => fake()->company().' Cosplay',
            'city' => fake()->randomElement(['Jakarta', 'Bandung', 'Surabaya', 'Yogyakarta', 'Medan']),
            'address' => fake()->address(),
            'legal_name' => fake()->name(),
            'legal_id_number' => (string) fake()->numerify('################'),
            'phone' => fake()->phoneNumber(),
            'status' => Tenant::STATUS_ACTIVE,
            'approved_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Tenant::STATUS_PENDING,
            'approved_at' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Tenant::STATUS_INACTIVE,
        ]);
    }
}
