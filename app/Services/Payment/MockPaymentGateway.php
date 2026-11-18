<?php

namespace App\Services\Payment;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Implementasi mock. TODO: Ganti dengan Midtrans/Xendit setelah final.
 */
class MockPaymentGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'mock';
    }

    public function createTransaction(Booking $booking, string $method): array
    {
        return [
            'external_id' => 'MOCK-'.strtoupper(Str::random(16)),
            'status' => 'pending',
            'instructions' => [
                'booking_code' => $booking->booking_code,
                'method' => $method,
                'message' => 'Pembayaran mock. Gunakan webhook atau tombol simulasi untuk menyelesaikan pembayaran.',
            ],
        ];
    }

    public function verifySignature(Request $request): bool
    {
        $signature = (string) ($request->header('X-Mock-Signature') ?: $request->input('signature', ''));
        $expected = $this->signature(
            (string) $request->input('external_id'),
            (string) $request->input('status')
        );

        return $signature !== '' && hash_equals($expected, $signature);
    }

    public function parseNotification(Request $request): array
    {
        return [
            'external_id' => (string) $request->input('external_id'),
            'status' => (string) $request->input('status'),
            'raw' => $request->all(),
        ];
    }

    public function signature(string $externalId, string $status): string
    {
        return hash_hmac(
            'sha256',
            $externalId.'|'.$status,
            (string) config('cosplayin.payment.mock.key')
        );
    }
}
