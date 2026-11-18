<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'customer@cosplayin.test'],
            [
                'name' => 'Siti Customer',
                'username' => 'customer',
                'password' => Hash::make('password'),
                'role' => User::ROLE_CUSTOMER,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
    }
}
