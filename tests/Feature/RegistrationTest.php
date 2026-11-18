<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_with_terms_and_privacy(): void
    {
        Notification::fake();

        $response = $this->post(route('register.store'), [
            'role' => 'customer',
            'name' => 'Customer Baru',
            'username' => 'customerbaru',
            'email' => 'customerbaru@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms_accepted' => '1',
            'privacy_accepted' => '1',
        ]);

        $response->assertRedirect(route('verification.notice'));

        $user = User::query()->where('email', 'customerbaru@example.test')->firstOrFail();

        $this->assertSame(User::ROLE_CUSTOMER, $user->role);
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertNull($user->email_verified_at);
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseMissing('tenants', ['user_id' => $user->id]);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_tenant_registration_creates_pending_tenant(): void
    {
        Notification::fake();

        $response = $this->post(route('register.store'), [
            'role' => 'tenant',
            'name' => 'Tenant Baru',
            'username' => 'tenantbaru',
            'email' => 'tenantbaru@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'store_name' => 'Toko Cosplay Baru',
            'city' => 'Bandung',
            'address' => 'Jl. Baru No. 2',
            'legal_name' => 'Pemilik Baru',
            'legal_id_number' => '3271234567890001',
            'phone' => '081200000000',
            'terms_accepted' => '1',
            'privacy_accepted' => '1',
        ]);

        $response->assertRedirect(route('verification.notice'));

        $user = User::query()->where('email', 'tenantbaru@example.test')->firstOrFail();

        $this->assertSame(User::ROLE_TENANT, $user->role);
        $this->assertDatabaseHas('tenants', [
            'user_id' => $user->id,
            'store_name' => 'Toko Cosplay Baru',
            'status' => 'pending',
        ]);
    }

    public function test_registration_requires_terms_and_privacy(): void
    {
        Notification::fake();

        $response = $this->post(route('register.store'), [
            'role' => 'customer',
            'name' => 'Tanpa Setuju',
            'username' => 'tanpasetuju',
            'email' => 'tanpasetuju@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['terms_accepted', 'privacy_accepted']);
        $this->assertDatabaseMissing('users', ['email' => 'tanpasetuju@example.test']);
    }

    public function test_customer_can_verify_email(): void
    {
        $user = User::factory()->unverified()->create();

        $url = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->actingAs($user)->get($url)->assertRedirect(route('dashboard'));

        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}
