<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCosplayData;
use Tests\TestCase;

class DashboardSmokeTest extends TestCase
{
    use CreatesCosplayData, RefreshDatabase;

    public function test_customer_dashboard_pages_render(): void
    {
        [$tenantUser, $tenant] = $this->createTenantUser();
        $customer = $this->createCustomer();
        $costume = $this->createCostume($tenant);

        $booking = $this->createBooking($customer, $tenant, $costume, [
            'status' => Booking::STATUS_WAITING_PAYMENT,
        ]);

        Payment::query()->create([
            'booking_id' => $booking->id,
            'method' => 'transfer',
            'external_id' => 'MOCK-SMOKE',
            'amount' => $booking->total,
            'status' => Payment::STATUS_PENDING,
        ]);

        $this->actingAs($customer)->get(route('dashboard'))->assertOk();
        $this->actingAs($customer)->get(route('orders.show', $booking))->assertOk();
        $this->actingAs($customer)->get(route('payment.show', $booking))->assertOk();
        $this->actingAs($customer)->get(route('notifications.index'))->assertOk();
    }

    public function test_tenant_dashboard_pages_render(): void
    {
        [$tenantUser, $tenant] = $this->createTenantUser();
        $customer = $this->createCustomer();
        $costume = $this->createCostume($tenant);
        $booking = $this->createBooking($customer, $tenant, $costume);

        $this->actingAs($tenantUser)->get(route('tenant.dashboard'))->assertOk();
        $this->actingAs($tenantUser)->get(route('tenant.costumes.index'))->assertOk();
        $this->actingAs($tenantUser)->get(route('tenant.costumes.create'))->assertOk();
        $this->actingAs($tenantUser)->get(route('tenant.costumes.edit', $costume))->assertOk();
        $this->actingAs($tenantUser)->get(route('tenant.categories.index'))->assertOk();
        $this->actingAs($tenantUser)->get(route('tenant.categories.create'))->assertOk();
        $this->actingAs($tenantUser)->get(route('tenant.bookings.index'))->assertOk();
        $this->actingAs($tenantUser)->get(route('tenant.orders.index'))->assertOk();
        $this->actingAs($tenantUser)->get(route('tenant.orders.show', $booking))->assertOk();
        $this->actingAs($tenantUser)->get(route('tenant.reports'))->assertOk();
        $this->actingAs($tenantUser)->get(route('tenant.profile.edit'))->assertOk();
    }

    public function test_admin_dashboard_pages_render(): void
    {
        $admin = User::factory()->admin()->create();
        [$tenantUser, $tenant] = $this->createTenantUser();
        $customer = $this->createCustomer();
        $costume = $this->createCostume($tenant);
        $booking = $this->createBooking($customer, $tenant, $costume);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('admin.tenants.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.transactions.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.transactions.show', $booking))->assertOk();
        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.settings.edit'))->assertOk();
    }

    public function test_tenant_can_create_and_update_costume(): void
    {
        [$tenantUser, $tenant] = $this->createTenantUser();

        $payload = [
            'character_name' => 'Kostum Baru',
            'description' => 'Deskripsi lengkap.',
            'accessories' => 'Wig.',
            'gender' => 'unisex',
            'city' => 'Bandung',
            'price_per_day' => 150000,
            'deposit' => 100000,
            'is_active' => '1',
            'stock_s' => 1,
            'stock_m' => 2,
            'stock_l' => 1,
            'stock_xl' => 0,
        ];

        $this->actingAs($tenantUser)
            ->post(route('tenant.costumes.store'), $payload)
            ->assertRedirect(route('tenant.costumes.index'));

        $costume = \App\Models\Costume::query()->where('character_name', 'Kostum Baru')->firstOrFail();

        $this->assertSame($tenant->id, $costume->tenant_id);
        $this->assertDatabaseHas('costume_sizes', [
            'costume_id' => $costume->id,
            'size' => 'M',
            'stock' => 2,
        ]);

        $this->actingAs($tenantUser)
            ->put(route('tenant.costumes.update', $costume), array_merge($payload, [
                'character_name' => 'Kostum Update',
                'stock_m' => 5,
            ]))
            ->assertRedirect(route('tenant.costumes.index'));

        $this->assertSame('Kostum Update', $costume->fresh()->character_name);
        $this->assertDatabaseHas('costume_sizes', [
            'costume_id' => $costume->id,
            'size' => 'M',
            'stock' => 5,
        ]);
    }

    public function test_other_tenant_cannot_modify_costume(): void
    {
        [$tenantUser, $tenant] = $this->createTenantUser();
        [$otherTenantUser, $otherTenant] = $this->createTenantUser();
        $costume = $this->createCostume($otherTenant);

        $this->actingAs($tenantUser)
            ->get(route('tenant.costumes.edit', $costume))
            ->assertForbidden();

        $this->actingAs($tenantUser)
            ->delete(route('tenant.costumes.destroy', $costume))
            ->assertForbidden();
    }

    public function test_inactive_tenant_is_blocked_from_dashboard(): void
    {
        [$tenantUser, $tenant] = $this->createTenantUser();
        $tenant->update(['status' => 'inactive']);

        $this->actingAs($tenantUser)
            ->get(route('tenant.dashboard'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('error');
    }
}
