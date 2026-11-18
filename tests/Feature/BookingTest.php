<?php

namespace Tests\Feature;

use App\Models\AvailabilityBlock;
use App\Models\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesCosplayData;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use CreatesCosplayData, RefreshDatabase;

    public function test_customer_can_create_booking_and_total_is_calculated_by_server(): void
    {
        Storage::fake('local');

        [$tenantUser, $tenant] = $this->createTenantUser();
        $customer = $this->createCustomer();
        $costume = $this->createCostume($tenant);

        $payload = $this->bookingPayload($costume, [
            'total' => 1,
            'rental_price' => 1,
        ]);

        $response = $this->actingAs($customer)->post(route('booking.store'), array_merge($payload, [
            'ktp' => UploadedFile::fake()->create('ktp.pdf', 100, 'application/pdf'),
        ]));

        $booking = Booking::query()->firstOrFail();

        $response->assertRedirect(route('orders.show', $booking));

        $this->assertSame(Booking::STATUS_WAITING_CONFIRMATION, $booking->status);
        $this->assertSame(2, $booking->duration_days);
        $this->assertSame(300000, $booking->rental_price);
        $this->assertSame(100000, $booking->deposit);
        $this->assertSame(25000, $booking->shipping_fee);
        $this->assertSame(425000, $booking->total);
        $this->assertSame($customer->id, $booking->customer_id);
        $this->assertSame($tenant->id, $booking->tenant_id);

        Storage::disk('local')->assertExists($booking->ktp_path);
        $this->assertDatabaseCount('availability_blocks', 2);
        $this->assertDatabaseHas('availability_blocks', [
            'costume_id' => $costume->id,
            'size' => 'M',
            'type' => AvailabilityBlock::TYPE_BOOKED,
            'booking_id' => $booking->id,
        ]);
    }

    public function test_double_booking_on_same_dates_is_prevented(): void
    {
        Storage::fake('local');

        [$tenantUser, $tenant] = $this->createTenantUser();
        $firstCustomer = $this->createCustomer();
        $secondCustomer = $this->createCustomer();
        $costume = $this->createCostume($tenant);

        $firstResponse = $this->actingAs($firstCustomer)->post(route('booking.store'), array_merge(
            $this->bookingPayload($costume),
            ['ktp' => UploadedFile::fake()->create('ktp.pdf', 100, 'application/pdf')]
        ));

        $firstResponse->assertSessionHasNoErrors();
        $firstResponse->assertRedirect();
        $this->assertDatabaseCount('bookings', 1);
        $this->assertDatabaseCount('availability_blocks', 2);
        $this->assertSame(2, AvailabilityBlock::query()->where('costume_id', $costume->id)->where('size', 'M')->count());
        $this->assertSame(2, AvailabilityBlock::query()->whereIn('date', [
            now()->addDay()->toDateString(),
            now()->addDays(2)->toDateString(),
        ])->count());
        $this->assertSame(0, app(\App\Services\AvailabilityService::class)->availableStock(
            $costume->id,
            'M',
            now()->addDay()->toDateString(),
            now()->addDays(3)->toDateString()
        ));

        $response = $this->actingAs($secondCustomer)->post(route('booking.store'), array_merge(
            $this->bookingPayload($costume),
            ['ktp' => UploadedFile::fake()->create('ktp2.pdf', 100, 'application/pdf')]
        ));

        $response->assertSessionHasErrors('ukuran');
        $this->assertDatabaseCount('bookings', 1);
        $this->assertDatabaseCount('availability_blocks', 2);
    }

    public function test_non_overlapping_dates_can_be_booked_for_same_size(): void
    {
        Storage::fake('local');

        [$tenantUser, $tenant] = $this->createTenantUser();
        $firstCustomer = $this->createCustomer();
        $secondCustomer = $this->createCustomer();
        $costume = $this->createCostume($tenant);

        $this->actingAs($firstCustomer)->post(route('booking.store'), array_merge(
            $this->bookingPayload($costume),
            ['ktp' => UploadedFile::fake()->create('ktp.pdf', 100, 'application/pdf')]
        ))->assertRedirect();

        $response = $this->actingAs($secondCustomer)->post(route('booking.store'), array_merge(
            $this->bookingPayload($costume, [
                'tanggal_mulai' => now()->addDays(3)->toDateString(),
                'tanggal_selesai' => now()->addDays(5)->toDateString(),
            ]),
            ['ktp' => UploadedFile::fake()->create('ktp2.pdf', 100, 'application/pdf')]
        ));

        $response->assertRedirect();
        $this->assertDatabaseCount('bookings', 2);
    }

    public function test_return_date_must_be_after_rental_date(): void
    {
        Storage::fake('local');

        [$tenantUser, $tenant] = $this->createTenantUser();
        $customer = $this->createCustomer();
        $costume = $this->createCostume($tenant);

        $response = $this->actingAs($customer)->post(route('booking.store'), array_merge(
            $this->bookingPayload($costume, [
                'tanggal_mulai' => now()->addDays(3)->toDateString(),
                'tanggal_selesai' => now()->addDay()->toDateString(),
                'ktp' => UploadedFile::fake()->create('ktp.pdf', 100, 'application/pdf'),
            ])
        ));

        $response->assertSessionHasErrors('tanggal_selesai');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_guest_cannot_create_booking(): void
    {
        [$tenantUser, $tenant] = $this->createTenantUser();
        $costume = $this->createCostume($tenant);

        $this->post(route('booking.store'), $this->bookingPayload($costume))
            ->assertRedirect(route('login'));
    }
}
