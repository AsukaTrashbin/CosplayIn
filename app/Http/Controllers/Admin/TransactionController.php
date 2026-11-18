<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TransactionFilterRequest;
use App\Http\Requests\Admin\VerifyPaymentRequest;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class TransactionController extends Controller
{
    public function index(TransactionFilterRequest $request): View
    {
        $filters = $request->validated();

        $bookings = Booking::query()
            ->with(['customer', 'tenant', 'costume', 'latestPayment'])
            ->when($filters['status'] ?? null, fn ($q, string $status) => $q->where('status', $status))
            ->when($filters['q'] ?? null, function ($query, string $keyword) {
                $query->where(function ($inner) use ($keyword) {
                    $inner->where('booking_code', 'like', '%'.$keyword.'%')
                        ->orWhere('booker_name', 'like', '%'.$keyword.'%')
                        ->orWhereHas('customer', fn ($c) => $c->where('email', 'like', '%'.$keyword.'%'));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $summary = [
            'total' => (int) Payment::query()->where('status', Payment::STATUS_SUCCESS)->sum('amount'),
            'pending' => Booking::query()->where('status', Booking::STATUS_WAITING_PAYMENT)->count(),
            'failed' => Booking::query()->where('status', Booking::STATUS_FAILED)->count(),
        ];

        return view('admin.transaksi.index', compact('bookings', 'filters', 'summary'));
    }

    public function show(Booking $booking): View
    {
        $booking->load(['customer', 'tenant.user', 'costume.images', 'payments', 'returnItem', 'reviews.reviewer']);

        return view('admin.transaksi.show', compact('booking'));
    }

    public function verifyPayment(
        VerifyPaymentRequest $request,
        Booking $booking,
        PaymentService $paymentService
    ): RedirectResponse {
        $paymentService->markSuccessManually($booking, $request->validated()['catatan'] ?? null);

        return back()->with('success', 'Pembayaran pesanan '.$booking->booking_code.' berhasil diverifikasi secara manual.');
    }
}
