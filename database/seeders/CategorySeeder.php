<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Anime', 'Game', 'Film', 'Tokoh Umum'] as $name) {
            Category::query()->firstOrCreate(
                ['tenant_id' => null, 'name' => $name]
            );
        }
    }
}
