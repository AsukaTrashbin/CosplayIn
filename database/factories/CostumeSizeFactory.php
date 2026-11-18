<?php

namespace Database\Factories;

use App\Models\CostumeSize;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CostumeSize>
 */
class CostumeSizeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'costume_id' => \App\Models\Costume::factory(),
            'size' => fake()->randomElement(['S', 'M', 'L', 'XL']),
            'stock' => 2,
        ];
    }
}
