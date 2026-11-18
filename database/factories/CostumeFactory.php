<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Costume;
use App\Models\CostumeSize;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Costume>
 */
class CostumeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'category_id' => Category::factory(),
            'character_name' => fake()->unique()->firstName().' '.fake()->randomElement(['Hero', 'Mage', 'Knight', 'Idol']),
            'description' => fake()->paragraph(),
            'accessories' => 'Wig, sepatu, dan aksesori pendukung.',
            'gender' => fake()->randomElement(['male', 'female', 'unisex']),
            'city' => fake()->randomElement(['Jakarta', 'Bandung', 'Surabaya', 'Yogyakarta']),
            'price_per_day' => 150000,
            'deposit' => 100000,
            'is_popular' => false,
            'is_active' => true,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Costume $costume) {
            foreach (Costume::SIZES as $size) {
                CostumeSize::query()->firstOrCreate(
                    ['costume_id' => $costume->id, 'size' => $size],
                    ['stock' => 2]
                );
            }
        });
    }

    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_popular' => true,
        ]);
    }
}
