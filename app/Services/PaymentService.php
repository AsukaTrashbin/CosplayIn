<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Setting;
use App\Services\Payment\PaymentGateway;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(
        private readonly PaymentGateway $gateway,
        private readonly NotificationService $notifications,
    ) {
    }

    public function createForBooking(Booking $booking, string $method): Payment
    {
        $payment = DB::transaction(function () use ($booking, $method) {
            $locked = Booking::query()->lockForUpdate()->findOrFail($booking->id);

            if (! in_array($locked->status, [Booking::STATUS_WAITING_PAYMENT, Booking::STATUS_FAILED], true)) {
                throw ValidationException::withMessages([
                    'metode' => 'Pembayaran belum dapat dilakukan pada status pesanan saat ini.',
                ]);
            }

            $pending = $locked->payments()
                ->where('status', Payment::STATUS_PENDING)
                ->latest()
                ->first();

            if ($pending) {
                return $pending;
            }

            $transaction = $this->gateway->createTransaction($locked, $method);

            return Payment::query()->create([
                'booking_id' => $locked->id,
                'method' => $method,
                'external_id' => $transaction['external_id'],
                'amount' => $locked->total,
                'status' => Payment::STATUS_PENDING,
                'gateway_response' => $transaction,
            ]);
        });

        return $payment;
    }

    public function markSuccess(Payment $payment, ?int $amount = null): void
    {
        $booking = DB::transaction(function () use ($payment, $amount) {
            $lockedPayment = Payment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($lockedPayment->status === Payment::STATUS_SUCCESS) {
                return null;
            }

            if ($amount !== null && (int) $amount !== $lockedPayment->amount) {
                throw ValidationException::withMessages([
                    'amount' => 'Nominal pembayaran tidak sesuai dengan tagihan.',
                ]);
            }

            $lockedPayment->update([
                'status' => Payment::STATUS_SUCCESS,
                'paid_at' => now(),
            ]);

            $booking = Booking::query()->lockForUpdate()->findOrFail($lockedPayment->booking_id);

            if (! in_array($booking->status, [Booking::STATUS_WAITING_PAYMENT, Booking::STATUS_FAILED], true)
                && $booking->status !== Booking::STATUS_PAID) {
                throw ValidationException::withMessages([
                    'status' => 'Pesanan tidak berada pada status yang dapat dibayar.',
                ]);
            }

            if ($booking->status !== Booking::STATUS_PAID) {
                $booking->update(['status' => Booking::STATUS_PAID]);
            }

            return $booking->fresh(['customer', 'tenant.user', 'costume']);
        });

        if ($booking) {
            $this->notifications->send(
                $booking->customer,
                'Pembayaran Berhasil',
                "Pembayaran untuk pesanan {$booking->booking_code} berhasil. Pesanan siap diproses oleh tenant.",
                route('orders.show', $booking)
            );

            $this->notifications->send(
                $booking->tenant->user,
                'Pembayaran Diterima',
                "Pembayaran pesanan {$booking->booking_code} telah diterima. Siapkan kostum untuk disewa.",
                route('tenant.orders.index')
            );
        }
    }

    public function markFailed(Payment $payment, string $status = Payment::STATUS_FAILED): void
    {
        $booking = DB::transaction(function () use ($payment, $status) {
            $lockedPayment = Payment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($lockedPayment->status === Payment::STATUS_SUCCESS) {
                return null;
            }

            $lockedPayment->update([
                'status' => $status === Payment::STATUS_EXPIRED ? Payment::STATUS_EXPIRED : Payment::STATUS_FAILED,
            ]);

            $booking = Booking::query()->lockForUpdate()->findOrFail($lockedPayment->booking_id);

            if ($booking->status === Booking::STATUS_WAITING_PAYMENT) {
                $booking->update(['status' => Booking::STATUS_FAILED]);
            }

            return $booking->fresh(['customer', 'tenant.user']);
        });

        if ($booking) {
            $this->notifications->send(
                $booking->customer,
                'Pembayaran Gagal',
                "Pembayaran untuk pesanan {$booking->booking_code} gagal atau kedaluwarsa. Silakan coba lagi.",
                route('payment.show', $booking)
            );

            $this->notifications->send(
                $booking->tenant->user,
                'Pembayaran Pesanan Gagal',
                "Pembayaran pesanan {$booking->booking_code} gagal. Menunggu pembayaran ulang dari customer.",
                route('tenant.orders.index')
            );
        }
    }

    public function markSuccessManually(Booking $booking, ?string $note = null): Payment
    {
        $payment = DB::transaction(function () use ($booking, $note) {
            $locked = Booking::query()->lockForUpdate()->findOrFail($booking->id);

            if (! in_array($locked->status, [Booking::STATUS_WAITING_PAYMENT, Booking::STATUS_FAILED], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Pembayaran hanya dapat diverifikasi pada pesanan yang menunggu pembayaran atau gagal.',
                ]);
            }

            $payment = $locked->payments()->where('status', Payment::STATUS_SUCCESS)->first();

            if (! $payment) {
                $payment = $locked->payments()->latest()->first();

                if ($payment) {
                    $payment->update([
                        'status' => Payment::STATUS_SUCCESS,
                        'paid_at' => now(),
                        'gateway_response' => array_merge($payment->gateway_response ?? [], [
                            'manual_verification' => true,
                            'note' => $note,
                        ]),
                    ]);
                } else {
                    $payment = Payment::query()->create([
                        'booking_id' => $locked->id,
                        'method' => 'transfer',
                        'external_id' => 'MANUAL-'.now()->format('YmdHis').'-'.$locked->id,
                        'amount' => $locked->total,
                        'status' => Payment::STATUS_SUCCESS,
                        'paid_at' => now(),
                        'gateway_response' => [
                            'manual_verification' => true,
                            'note' => $note,
                        ],
                    ]);
                }
            }

            $locked->update(['status' => Booking::STATUS_PAID]);

            return $payment;
        });

        $booking->refresh()->loadMissing(['customer', 'tenant.user']);
        $this->notifications->send(
            $booking->customer,
            'Pembayaran Diverifikasi Admin',
            "Pembayaran pesanan {$booking->booking_code} telah diverifikasi secara manual oleh admin.",
            route('orders.show', $booking)
        );
        $this->notifications->send(
            $booking->tenant->user,
            'Pembayaran Diverifikasi Admin',
            "Pembayaran pesanan {$booking->booking_code} telah diverifikasi. Siapkan kostum.",
            route('tenant.orders.index')
        );

        return $payment;
    }

    public function notifyPreparing(Booking $booking): void
    {
        $this->notifications->send(
            $booking->customer,
            'Kostum Sedang Disiapkan',
            "Tenant sedang menyiapkan kostum untuk pesanan {$booking->booking_code}.",
            route('orders.show', $booking)
        );

        Log::info('Tenant preparing booking', ['booking_id' => $booking->id]);
    }

    public function platformCommissionPercent(): float
    {
        return (float) Setting::get('platform_commission_percent', config('cosplayin.commission_percent', 10));
    }
}
