<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCosplayData;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use CreatesCosplayData, RefreshDatabase;

    public function test_customer_can_login_with_username_and_redirect_to_dashboard(): void
    {
        $customer = $this->createCustomer();

        $response = $this->post(route('login.store'), [
            'login' => $customer->username,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($customer);
    }

    public function test_login_redirects_back_to_intended_booking_page(): void
    {
        [$tenantUser, $tenant] = $this->createTenantUser();
        $costume = $this->createCostume($tenant);
        $customer = $this->createCustomer();

        $this->get(route('booking.create', $costume))
            ->assertRedirect(route('login'));

        $response = $this->post(route('login.store'), [
            'login' => $customer->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('booking.create', $costume));
        $this->assertAuthenticatedAs($customer);
    }

    public function test_tenant_login_redirects_to_tenant_dashboard(): void
    {
        [$tenantUser] = $this->createTenantUser();

        $response = $this->post(route('login.store'), [
            'login' => $tenantUser->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('tenant.dashboard'));
        $this->assertAuthenticatedAs($tenantUser);
    }

    public function test_customer_cannot_access_tenant_or_admin_dashboard(): void
    {
        $customer = $this->createCustomer();

        $this->actingAs($customer)->get(route('tenant.dashboard'))->assertForbidden();
        $this->actingAs($customer)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_guest_is_redirected_to_login_when_accessing_dashboard(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_logout_invalidates_session(): void
    {
        $customer = $this->createCustomer();

        $this->actingAs($customer)->post(route('logout'))->assertRedirect(route('home'));
        $this->assertGuest();
    }
}
