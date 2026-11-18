<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingFilterRequest;
use App\Models\Booking;
use App\Services\NotificationService;
use App\Services\PaymentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(BookingFilterRequest $request): View
    {
        $tenant = $request->user()->tenant;
        $filters = $request->validated();

        $bookings = Booking::query()
            ->where('tenant_id', $tenant->id)
            ->with(['customer', 'costume.images', 'latestPayment', 'returnItem'])
            ->when($filters['status'] ?? null, fn ($q, string $status) => $q->where('status', $status))
            ->when($filters['q'] ?? null, function ($query, string $keyword) {
                $query->where(function ($inner) use ($keyword) {
                    $inner->where('booking_code', 'like', '%'.$keyword.'%')
                        ->orWhere('booker_name', 'like', '%'.$keyword.'%');
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('tenant.pesanan.index', compact('bookings', 'filters'));
    }

    public function show(Booking $booking, PaymentService $paymentService): View
    {
        $this->authorize('manage', $booking);

        $booking->load([
            'customer',
            'costume.images',
            'payments',
            'returnItem',
            'reviews.reviewer',
        ]);

        $canReview = $booking->canBeReviewedBy(request()->user());
        $reviewTarget = $booking->reviewTargetFor(request()->user());

        return view('tenant.pesanan.show', compact('booking', 'canReview', 'reviewTarget'));
    }

    public function markReady(Booking $booking, NotificationService $notifications): RedirectResponse
    {
        $this->authorize('manage', $booking);

        DB::transaction(function () use ($booking) {
            $locked = Booking::query()->lockForUpdate()->findOrFail($booking->id);

            if ($locked->status !== Booking::STATUS_PAID) {
                throw ValidationException::withMessages([
                    'status' => 'Pesanan harus berstatus dibayar sebelum ditandai siap diproses.',
                ]);
            }

            $locked->update(['status' => Booking::STATUS_READY_TO_PROCESS]);
        });

        $booking->refresh()->loadMissing(['customer']);
        $notifications->send(
            $booking->customer,
            'Kostum Sedang Disiapkan',
            "Tenant sedang menyiapkan kostum untuk pesanan {$booking->booking_code}.",
            route('orders.show', $booking)
        );

        return back()->with('success', 'Pesanan ditandai siap diproses.');
    }

    public function markRented(Booking $booking, NotificationService $notifications): RedirectResponse
    {
        $this->authorize('manage', $booking);

        DB::transaction(function () use ($booking) {
            $locked = Booking::query()->lockForUpdate()->findOrFail($booking->id);

            if (! in_array($locked->status, [Booking::STATUS_PAID, Booking::STATUS_READY_TO_PROCESS], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Pesanan belum dapat ditandai sedang disewa.',
                ]);
            }

            $locked->update(['status' => Booking::STATUS_RENTED]);
        });

        $booking->refresh()->loadMissing(['customer']);
        $notifications->send(
            $booking->customer,
            'Kostum Sedang Disewa',
            "Pesanan {$booking->booking_code} telah diserahkan/dikirim. Selamat menggunakan kostum.",
            route('orders.show', $booking)
        );

        return back()->with('success', 'Pesanan ditandai sedang disewa.');
    }
}
