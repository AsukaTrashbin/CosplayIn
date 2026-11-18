<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\Payment\MockPaymentGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCosplayData;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
    use CreatesCosplayData, RefreshDatabase;

    public function test_success_webhook_marks_booking_as_paid(): void
    {
        [$tenantUser, $tenant] = $this->createTenantUser();
        $customer = $this->createCustomer();
        $costume = $this->createCostume($tenant);

        $booking = $this->createBooking($customer, $tenant, $costume, [
            'status' => Booking::STATUS_WAITING_PAYMENT,
        ]);

        $payment = Payment::query()->create([
            'booking_id' => $booking->id,
            'method' => 'transfer',
            'external_id' => 'MOCK-TEST-SUCCESS',
            'amount' => $booking->total,
            'status' => Payment::STATUS_PENDING,
        ]);

        $signature = app(MockPaymentGateway::class)->signature('MOCK-TEST-SUCCESS', 'success');

        $response = $this->postJson(route('webhook.payment'), [
            'external_id' => 'MOCK-TEST-SUCCESS',
            'status' => 'success',
            'amount' => $booking->total,
        ], ['X-Mock-Signature' => $signature]);

        $response->assertOk()->assertJson(['payment_status' => 'success']);

        $this->assertSame(Payment::STATUS_SUCCESS, $payment->fresh()->status);
        $this->assertNotNull($payment->fresh()->paid_at);
        $this->assertSame(Booking::STATUS_PAID, $booking->fresh()->status);
        $this->assertDatabaseCount('notifications', 2);
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        [$tenantUser, $tenant] = $this->createTenantUser();
        $customer = $this->createCustomer();
        $costume = $this->createCostume($tenant);
        $booking = $this->createBooking($customer, $tenant, $costume);

        Payment::query()->create([
            'booking_id' => $booking->id,
            'method' => 'qris',
            'external_id' => 'MOCK-TEST-INVALID',
            'amount' => $booking->total,
            'status' => Payment::STATUS_PENDING,
        ]);

        $this->postJson(route('webhook.payment'), [
            'external_id' => 'MOCK-TEST-INVALID',
            'status' => 'success',
        ], ['X-Mock-Signature' => 'signature-palsu'])
            ->assertStatus(401);

        $this->assertSame(Booking::STATUS_WAITING_PAYMENT, $booking->fresh()->status);
    }

    public function test_failed_webhook_sets_booking_failed(): void
    {
        [$tenantUser, $tenant] = $this->createTenantUser();
        $customer = $this->createCustomer();
        $costume = $this->createCostume($tenant);
        $booking = $this->createBooking($customer, $tenant, $costume);

        $payment = Payment::query()->create([
            'booking_id' => $booking->id,
            'method' => 'ewallet',
            'external_id' => 'MOCK-TEST-FAILED',
            'amount' => $booking->total,
            'status' => Payment::STATUS_PENDING,
        ]);

        $signature = app(MockPaymentGateway::class)->signature('MOCK-TEST-FAILED', 'failed');

        $this->postJson(route('webhook.payment'), [
            'external_id' => 'MOCK-TEST-FAILED',
            'status' => 'failed',
        ], ['X-Mock-Signature' => $signature])->assertOk();

        $this->assertSame(Payment::STATUS_FAILED, $payment->fresh()->status);
        $this->assertSame(Booking::STATUS_FAILED, $booking->fresh()->status);
    }

    public function test_webhook_rejects_amount_mismatch(): void
    {
        [$tenantUser, $tenant] = $this->createTenantUser();
        $customer = $this->createCustomer();
        $costume = $this->createCostume($tenant);
        $booking = $this->createBooking($customer, $tenant, $costume);

        Payment::query()->create([
            'booking_id' => $booking->id,
            'method' => 'transfer',
            'external_id' => 'MOCK-TEST-AMOUNT',
            'amount' => $booking->total,
            'status' => Payment::STATUS_PENDING,
        ]);

        $signature = app(MockPaymentGateway::class)->signature('MOCK-TEST-AMOUNT', 'success');

        $this->postJson(route('webhook.payment'), [
            'external_id' => 'MOCK-TEST-AMOUNT',
            'status' => 'success',
            'amount' => 1000,
        ], ['X-Mock-Signature' => $signature])->assertStatus(422);

        $this->assertSame(Booking::STATUS_WAITING_PAYMENT, $booking->fresh()->status);
    }

    public function test_customer_can_retry_payment_after_failure(): void
    {
        [$tenantUser, $tenant] = $this->createTenantUser();
        $customer = $this->createCustomer();
        $costume = $this->createCostume($tenant);
        $booking = $this->createBooking($customer, $tenant, $costume, [
            'status' => Booking::STATUS_FAILED,
        ]);

        $this->actingAs($customer)
            ->post(route('payment.store', $booking), ['metode' => 'qris'])
            ->assertRedirect(route('payment.show', $booking));

        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'method' => 'qris',
            'status' => Payment::STATUS_PENDING,
        ]);
    }
}
