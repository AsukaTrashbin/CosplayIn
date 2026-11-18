<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentStoreRequest;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function show(Booking $booking): View
    {
        $this->authorize('view', $booking);

        $booking->load(['costume.images', 'tenant', 'payments']);

        return view('pembayaran.show', [
            'booking' => $booking,
            'latestPayment' => $booking->payments()->latest()->first(),
            'bankAccounts' => config('cosplayin.bank_accounts'),
            'allowMockSuccess' => (bool) config('cosplayin.payment.mock.allow_mock_success'),
        ]);
    }

    public function store(PaymentStoreRequest $request, Booking $booking, PaymentService $paymentService): RedirectResponse
    {
        $this->authorize('view', $booking);

        if ($booking->customer_id !== $request->user()->id) {
            abort(403);
        }

        $payment = $paymentService->createForBooking($booking, $request->validated()['metode']);

        return redirect()
            ->route('payment.show', $booking)
            ->with('success', 'Instruksi pembayaran dibuat. ID transaksi: '.$payment->external_id);
    }

    public function mockSuccess(Booking $booking, PaymentService $paymentService): RedirectResponse
    {
        $this->authorize('view', $booking);

        abort_unless(config('cosplayin.payment.mock.allow_mock_success'), 403);

        if (! in_array($booking->status, [Booking::STATUS_WAITING_PAYMENT, Booking::STATUS_FAILED], true)) {
            throw ValidationException::withMessages([
                'status' => 'Pesanan tidak dapat dibayar pada status saat ini.',
            ]);
        }

        $payment = $booking->payments()->where('status', Payment::STATUS_PENDING)->latest()->first();

        if (! $payment) {
            $payment = $booking->payments()->create([
                'method' => 'qris',
                'external_id' => 'MOCK-SIM-'.strtoupper(uniqid()),
                'amount' => $booking->total,
                'status' => Payment::STATUS_PENDING,
            ]);
        }

        $paymentService->markSuccess($payment);

        return redirect()
            ->route('orders.show', $booking)
            ->with('success', 'Simulasi pembayaran berhasil. Status pesanan diperbarui.');
    }
}
