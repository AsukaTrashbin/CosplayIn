<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => 'tenant@cosplayin.test'],
            [
                'name' => 'Budi Tenant',
                'username' => 'tenant',
                'password' => Hash::make('password'),
                'role' => User::ROLE_TENANT,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        Tenant::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'store_name' => 'Cosplay Kingdom',
                'city' => 'Jakarta',
                'address' => 'Jl. Cosplay Raya No. 10, Jakarta Selatan',
                'legal_name' => 'Budi Santoso',
                'legal_id_number' => '3171234567890001',
                'phone' => '081234567890',
                'status' => Tenant::STATUS_ACTIVE,
                'approved_at' => now(),
            ]
        );
    }
}
