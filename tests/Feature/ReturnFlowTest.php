<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\ReturnItem;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCosplayData;
use Tests\TestCase;

class ReturnFlowTest extends TestCase
{
    use CreatesCosplayData, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::put('late_fee_per_day', 50000);
        Setting::put('max_late_fee', 500000);
    }

    public function test_customer_and_tenant_complete_return_with_late_fee(): void
    {
        [$tenantUser, $tenant] = $this->createTenantUser();
        $customer = $this->createCustomer();
        $costume = $this->createCostume($tenant);

        $booking = $this->createBooking($customer, $tenant, $costume, [
            'status' => Booking::STATUS_RENTED,
            'rental_date' => now()->subDays(5)->toDateString(),
            'return_date' => now()->subDays(2)->toDateString(),
            'deposit' => 100000,
        ]);

        $this->actingAs($customer)
            ->post(route('returns.store', $booking), [
                'metode' => 'courier',
                'nomor_resi' => 'RESI-12345',
            ])
            ->assertRedirect(route('orders.show', $booking));

        $this->assertSame(Booking::STATUS_RETURNED, $booking->fresh()->status);
        $this->assertDatabaseHas('returns', [
            'booking_id' => $booking->id,
            'method' => 'courier',
            'tracking_number' => 'RESI-12345',
        ]);

        $this->actingAs($tenantUser)
            ->post(route('tenant.returns.verify', $booking), [
                'tanggal_kembali_aktual' => now()->toDateString(),
                'kondisi' => 'good',
                'klaim_kerusakan' => 0,
                'catatan_tenant' => 'Lengkap.',
            ])
            ->assertRedirect(route('tenant.orders.show', $booking));

        $return = ReturnItem::query()->where('booking_id', $booking->id)->firstOrFail();

        $this->assertSame(Booking::STATUS_COMPLETED, $booking->fresh()->status);
        $this->assertSame(2, $return->days_late);
        $this->assertSame(100000, $return->late_fee);
        $this->assertSame(0, $return->damage_claim);
        $this->assertSame(0, $return->deposit_deduction);
        $this->assertSame(100000, $return->deposit_refunded);

        $this->assertDatabaseCount('notifications', 3);
        $this->assertDatabaseCount('availability_blocks', 0);
    }

    public function test_damage_claim_is_deducted_from_deposit(): void
    {
        [$tenantUser, $tenant] = $this->createTenantUser();
        $customer = $this->createCustomer();
        $costume = $this->createCostume($tenant);

        $booking = $this->createBooking($customer, $tenant, $costume, [
            'status' => Booking::STATUS_RENTED,
            'rental_date' => now()->subDays(3)->toDateString(),
            'return_date' => now()->toDateString(),
            'deposit' => 100000,
        ]);

        $this->actingAs($customer)
            ->post(route('returns.store', $booking), [
                'metode' => 'cod',
                'lokasi_cod' => 'Stasiun Jakarta',
                'waktu_cod' => '14:00',
            ])
            ->assertRedirect();

        $this->actingAs($tenantUser)
            ->post(route('tenant.returns.verify', $booking), [
                'tanggal_kembali_aktual' => now()->toDateString(),
                'kondisi' => 'damaged_lost',
                'klaim_kerusakan' => 150000,
            ])
            ->assertRedirect();

        $return = ReturnItem::query()->where('booking_id', $booking->id)->firstOrFail();

        $this->assertSame(Booking::STATUS_COMPLETED, $booking->fresh()->status);
        $this->assertSame(150000, $return->damage_claim);
        $this->assertSame(100000, $return->deposit_deduction);
        $this->assertSame(0, $return->deposit_refunded);
    }

    public function test_return_cannot_start_before_rental_period_ends(): void
    {
        [$tenantUser, $tenant] = $this->createTenantUser();
        $customer = $this->createCustomer();
        $costume = $this->createCostume($tenant);

        $booking = $this->createBooking($customer, $tenant, $costume, [
            'status' => Booking::STATUS_RENTED,
            'return_date' => now()->addDays(3)->toDateString(),
        ]);

        $this->actingAs($customer)
            ->get(route('returns.create', $booking))
            ->assertRedirect(route('orders.show', $booking))
            ->assertSessionHas('error');
    }
}
