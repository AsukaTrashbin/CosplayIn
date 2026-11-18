<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Http\Requests\WebhookPaymentRequest;
use App\Models\Payment;
use App\Services\Payment\PaymentGateway;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class PaymentWebhookController extends Controller
{
    public function handle(
        WebhookPaymentRequest $request,
        PaymentGateway $gateway,
        PaymentService $paymentService
    ): JsonResponse {
        if (! $gateway->verifySignature($request)) {
            return response()->json(['message' => 'Signature tidak valid.'], 401);
        }

        $notification = $gateway->parseNotification($request);

        $payment = Payment::query()
            ->where('external_id', $notification['external_id'])
            ->first();

        if (! $payment) {
            return response()->json(['message' => 'Pembayaran tidak ditemukan.'], 404);
        }

        try {
            if ($notification['status'] === 'success') {
                $paymentService->markSuccess($payment, $request->input('amount') !== null ? (int) $request->input('amount') : null);
            } elseif (in_array($notification['status'], ['failed', 'expired'], true)) {
                $paymentService->markFailed($payment, $notification['status']);
            }
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => 'Notifikasi tidak dapat diproses.',
                'errors' => $exception->errors(),
            ], 422);
        }

        return response()->json([
            'message' => 'Notifikasi pembayaran diterima.',
            'payment_status' => $payment->fresh()->status,
        ]);
    }
}
