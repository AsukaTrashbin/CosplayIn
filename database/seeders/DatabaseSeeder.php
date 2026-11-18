<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            TenantSeeder::class,
            CustomerSeeder::class,
            CategorySeeder::class,
            CostumeSeeder::class,
            SettingSeeder::class,
        ]);
    }
}
