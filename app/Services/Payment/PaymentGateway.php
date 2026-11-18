<?php

namespace App\Services\Payment;

use App\Models\Booking;
use Illuminate\Http\Request;

interface PaymentGateway
{
    public function name(): string;

    /**
     * @return array{external_id:string, status:string, instructions:array<string, mixed>}
     */
    public function createTransaction(Booking $booking, string $method): array;

    /**
     * Verifikasi signature webhook. Dipisahkan agar mudah diganti
     * dengan implementasi Midtrans/Xendit.
     */
    public function verifySignature(Request $request): bool;

    /**
     * @return array{external_id:string, status:string, raw:array<string, mixed>}
     */
    public function parseNotification(Request $request): array;
}
