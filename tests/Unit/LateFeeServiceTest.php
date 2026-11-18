<?php

namespace Tests\Unit;

use App\Models\Setting;
use App\Services\LateFeeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCosplayData;
use Tests\TestCase;

class LateFeeServiceTest extends TestCase
{
    use CreatesCosplayData, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::put('late_fee_per_day', 50000);
        Setting::put('max_late_fee', 500000);
    }

    public function test_it_calculates_late_fee_per_day(): void
    {
        [, $tenant] = $this->createTenantUser();
        $customer = $this->createCustomer();
        $costume = $this->createCostume($tenant);

        $booking = $this->createBooking($customer, $tenant, $costume, [
            'return_date' => '2026-01-10',
        ]);

        $result = app(LateFeeService::class)->calculate($booking, '2026-01-13');

        $this->assertSame(3, $result['days_late']);
        $this->assertSame(150000, $result['late_fee']);
        $this->assertSame(50000, $result['rate']);
        $this->assertSame(500000, $result['maximum']);
    }

    public function test_late_fee_is_capped_at_maximum(): void
    {
        [, $tenant] = $this->createTenantUser();
        $customer = $this->createCustomer();
        $costume = $this->createCostume($tenant);

        $booking = $this->createBooking($customer, $tenant, $costume, [
            'return_date' => '2026-01-10',
        ]);

        $result = app(LateFeeService::class)->calculate($booking, '2026-02-10');

        $this->assertSame(31, $result['days_late']);
        $this->assertSame(500000, $result['late_fee']);
    }

    public function test_no_late_fee_when_returned_on_time_or_early(): void
    {
        [, $tenant] = $this->createTenantUser();
        $customer = $this->createCustomer();
        $costume = $this->createCostume($tenant);

        $booking = $this->createBooking($customer, $tenant, $costume, [
            'return_date' => '2026-01-10',
        ]);

        $service = app(LateFeeService::class);

        $onTime = $service->calculate($booking, '2026-01-10');
        $early = $service->calculate($booking, '2026-01-08');

        $this->assertSame(0, $onTime['days_late']);
        $this->assertSame(0, $onTime['late_fee']);
        $this->assertSame(0, $early['days_late']);
        $this->assertSame(0, $early['late_fee']);
    }
}
