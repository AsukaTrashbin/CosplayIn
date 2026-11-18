<?php

namespace Tests\Feature;

use App\Models\AvailabilityBlock;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCosplayData;
use Tests\TestCase;

class TenantAndAdminFlowTest extends TestCase
{
    use CreatesCosplayData, RefreshDatabase;

    public function test_tenant_can_approve_booking_request(): void
    {
        [$tenantUser, $tenant] = $this->createTenantUser();
        $customer = $this->createCustomer();
        $costume = $this->createCostume($tenant);

        $booking = $this->createBooking($customer, $tenant, $costume, [
            'status' => Booking::STATUS_WAITING_CONFIRMATION,
        ]);

        $this->actingAs($tenantUser)
            ->post(route('tenant.bookings.approve', $booking))
            ->assertRedirect();

        $this->assertSame(Booking::STATUS_WAITING_PAYMENT, $booking->fresh()->status);
        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_tenant_can_reject_and_release_availability_blocks(): void
    {
        [$tenantUser, $tenant] = $this->createTenantUser();
        $customer = $this->createCustomer();
        $costume = $this->createCostume($tenant);

        $booking = $this->createBooking($customer, $tenant, $costume, [
            'status' => Booking::STATUS_WAITING_CONFIRMATION,
        ]);

        AvailabilityBlock::query()->create([
            'costume_id' => $costume->id,
            'size' => 'M',
            'date' => $booking->rental_date->toDateString(),
            'type' => AvailabilityBlock::TYPE_BOOKED,
            'booking_id' => $booking->id,
        ]);

        $this->actingAs($tenantUser)
            ->post(route('tenant.bookings.reject', $booking), [
                'alasan_penolakan' => 'Jadwal bentrok.',
            ])
            ->assertRedirect();

        $this->assertSame(Booking::STATUS_REJECTED, $booking->fresh()->status);
        $this->assertDatabaseCount('availability_blocks', 0);
    }

    public function test_tenant_cannot_manage_other_tenants_booking(): void
    {
        [$tenantUser, $tenant] = $this->createTenantUser();
        [$otherTenantUser, $otherTenant] = $this->createTenantUser();
        $customer = $this->createCustomer();
        $costume = $this->createCostume($otherTenant);

        $booking = $this->createBooking($customer, $otherTenant, $costume, [
            'status' => Booking::STATUS_WAITING_CONFIRMATION,
        ]);

        $this->actingAs($tenantUser)
            ->post(route('tenant.bookings.approve', $booking))
            ->assertForbidden();
    }

    public function test_admin_can_manually_verify_payment(): void
    {
        $admin = User::factory()->admin()->create();
        [$tenantUser, $tenant] = $this->createTenantUser();
        $customer = $this->createCustomer();
        $costume = $this->createCostume($tenant);

        $booking = $this->createBooking($customer, $tenant, $costume, [
            'status' => Booking::STATUS_WAITING_PAYMENT,
        ]);

        Payment::query()->create([
            'booking_id' => $booking->id,
            'method' => 'transfer',
            'external_id' => 'MANUAL-TEST',
            'amount' => $booking->total,
            'status' => Payment::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.transactions.verify-payment', $booking), [
                'catatan' => 'Bukti transfer valid.',
            ])
            ->assertRedirect();

        $this->assertSame(Booking::STATUS_PAID, $booking->fresh()->status);
        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'status' => Payment::STATUS_SUCCESS,
        ]);
    }

    public function test_admin_can_approve_tenant_registration(): void
    {
        $admin = User::factory()->admin()->create();
        $tenantUser = User::factory()->tenant()->create();
        $tenant = Tenant::factory()->pending()->create(['user_id' => $tenantUser->id]);

        $this->actingAs($admin)
            ->post(route('admin.tenants.approve', $tenant))
            ->assertRedirect();

        $this->assertSame('active', $tenant->fresh()->status);
        $this->assertNotNull($tenant->fresh()->approved_at);
    }

    public function test_tenant_can_block_and_unblock_maintenance_date(): void
    {
        [$tenantUser, $tenant] = $this->createTenantUser();
        $costume = $this->createCostume($tenant);
        $date = now()->addDays(2)->toDateString();

        $this->actingAs($tenantUser)
            ->post(route('tenant.costumes.schedule.store', $costume), [
                'ukuran' => 'M',
                'tanggal' => $date,
                'catatan' => 'Perbaikan jahitan.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('availability_blocks', [
            'costume_id' => $costume->id,
            'size' => 'M',
            'type' => AvailabilityBlock::TYPE_MAINTENANCE,
        ]);

        $block = AvailabilityBlock::query()->firstOrFail();

        $this->actingAs($tenantUser)
            ->get(route('tenant.costumes.schedule', $costume))
            ->assertOk()
            ->assertSee('Perbaikan jahitan.');

        $this->actingAs($tenantUser)
            ->delete(route('tenant.costumes.schedule.destroy', [$costume, $block]))
            ->assertRedirect();

        $this->assertDatabaseCount('availability_blocks', 0);
    }

    public function test_tenant_cannot_delete_booking_block_manually(): void
    {
        [$tenantUser, $tenant] = $this->createTenantUser();
        $customer = $this->createCustomer();
        $costume = $this->createCostume($tenant);

        $booking = $this->createBooking($customer, $tenant, $costume);

        $block = AvailabilityBlock::query()->create([
            'costume_id' => $costume->id,
            'size' => 'M',
            'date' => $booking->rental_date->toDateString(),
            'type' => AvailabilityBlock::TYPE_BOOKED,
            'booking_id' => $booking->id,
        ]);

        $this->actingAs($tenantUser)
            ->delete(route('tenant.costumes.schedule.destroy', [$costume, $block]))
            ->assertForbidden();

        $this->assertDatabaseHas('availability_blocks', ['id' => $block->id]);
    }

    public function test_tenant_cannot_manage_other_tenants_schedule(): void
    {
        [$tenantUser] = $this->createTenantUser();
        [, $otherTenant] = $this->createTenantUser();
        $costume = $this->createCostume($otherTenant);

        $this->actingAs($tenantUser)
            ->get(route('tenant.costumes.schedule', $costume))
            ->assertForbidden();

        $this->actingAs($tenantUser)
            ->post(route('tenant.costumes.schedule.store', $costume), [
                'ukuran' => 'M',
                'tanggal' => now()->addDays(2)->toDateString(),
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('availability_blocks', 0);
    }

    public function test_review_can_only_be_left_after_completion_and_once(): void
    {
        [$tenantUser, $tenant] = $this->createTenantUser();
        $customer = $this->createCustomer();
        $costume = $this->createCostume($tenant);

        $booking = $this->createBooking($customer, $tenant, $costume, [
            'status' => Booking::STATUS_COMPLETED,
        ]);

        $this->actingAs($customer)
            ->post(route('reviews.store', $booking), ['rating' => 5, 'comment' => 'Mantap.'])
            ->assertRedirect();

        $this->assertDatabaseHas('reviews', [
            'booking_id' => $booking->id,
            'reviewer_id' => $customer->id,
            'target' => 'tenant',
            'rating' => 5,
        ]);

        $this->actingAs($customer)
            ->post(route('reviews.store', $booking), ['rating' => 4])
            ->assertSessionHasErrors('rating');

        $this->assertDatabaseCount('reviews', 1);
    }
}
