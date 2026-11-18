<?php

namespace Tests\Concerns;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Costume;
use App\Models\CostumeSize;
use App\Models\Tenant;
use App\Models\User;

trait CreatesCosplayData
{
    protected function createTenantUser(): array
    {
        $user = User::factory()->tenant()->create();

        $tenant = Tenant::query()->create([
            'user_id' => $user->id,
            'store_name' => 'Cosplay Test Store',
            'city' => 'Jakarta',
            'address' => 'Jl. Test No. 1',
            'legal_name' => 'Pemilik Test',
            'legal_id_number' => '3171234567890001',
            'phone' => '081234567890',
            'status' => Tenant::STATUS_ACTIVE,
            'approved_at' => now(),
        ]);

        return [$user, $tenant];
    }

    protected function createCustomer(): User
    {
        return User::factory()->customer()->create();
    }

    protected function createCostume(Tenant $tenant, array $overrides = []): Costume
    {
        $category = Category::query()->firstOrCreate(
            ['tenant_id' => null, 'name' => 'Anime'],
        );

        $costume = Costume::query()->create(array_merge([
            'tenant_id' => $tenant->id,
            'category_id' => $category->id,
            'character_name' => 'Test Character',
            'description' => 'Kostum untuk pengujian.',
            'accessories' => 'Wig dan aksesori.',
            'gender' => 'unisex',
            'city' => 'Jakarta',
            'price_per_day' => 150000,
            'deposit' => 100000,
            'is_popular' => false,
            'is_active' => true,
        ], $overrides));

        foreach (Costume::SIZES as $size) {
            CostumeSize::query()->create([
                'costume_id' => $costume->id,
                'size' => $size,
                'stock' => $size === 'M' ? 1 : 0,
            ]);
        }

        return $costume;
    }

    protected function createBooking(User $customer, Tenant $tenant, Costume $costume, array $overrides = []): Booking
    {
        return Booking::query()->create(array_merge([
            'customer_id' => $customer->id,
            'tenant_id' => $tenant->id,
            'costume_id' => $costume->id,
            'size' => 'M',
            'booker_name' => $customer->name,
            'identity_number' => '3171234567890001',
            'phone' => '081234567890',
            'address' => 'Jl. Test No. 1',
            'rental_date' => now()->addDay()->toDateString(),
            'return_date' => now()->addDays(3)->toDateString(),
            'duration_days' => 2,
            'rental_price' => 300000,
            'deposit' => 100000,
            'shipping_fee' => 25000,
            'total' => 425000,
            'status' => Booking::STATUS_WAITING_PAYMENT,
            'shipping_method' => 'courier',
            'ktp_path' => 'ktp/test-ktp.pdf',
        ], $overrides));
    }

    protected function bookingPayload(Costume $costume, array $overrides = []): array
    {
        return array_merge([
            'costume_id' => $costume->id,
            'nama_penyewa' => 'Penyewa Test',
            'nomor_identitas' => '3171234567890001',
            'telepon' => '081234567890',
            'alamat' => 'Jl. Test No. 1',
            'tanggal_mulai' => now()->addDay()->toDateString(),
            'tanggal_selesai' => now()->addDays(3)->toDateString(),
            'ukuran' => 'M',
            'metode_pengiriman' => 'courier',
        ], $overrides);
    }
}
