<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingFilterRequest;
use App\Http\Requests\Tenant\RejectBookingRequest;
use App\Models\Booking;
use App\Services\AvailabilityService;
use App\Services\NotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function index(BookingFilterRequest $request): View
    {
        $tenant = $request->user()->tenant;
        $filters = $request->validated();

        $bookings = Booking::query()
            ->where('tenant_id', $tenant->id)
            ->with(['customer', 'costume.images', 'latestPayment'])
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

        return view('tenant.pengajuan.index', compact('bookings', 'filters'));
    }

    public function approve(Booking $booking, NotificationService $notifications): RedirectResponse
    {
        $this->authorize('manage', $booking);

        DB::transaction(function () use ($booking) {
            $locked = Booking::query()->lockForUpdate()->findOrFail($booking->id);

            if ($locked->status !== Booking::STATUS_WAITING_CONFIRMATION) {
                throw ValidationException::withMessages([
                    'status' => 'Pengajuan ini tidak berada pada status menunggu konfirmasi.',
                ]);
            }

            $locked->update(['status' => Booking::STATUS_WAITING_PAYMENT]);
        });

        $booking->refresh()->loadMissing(['customer', 'tenant.user', 'costume']);

        $notifications->send(
            $booking->customer,
            'Pengajuan Sewa Disetujui',
            "Pengajuan sewa {$booking->booking_code} disetujui. Silakan lanjutkan pembayaran.",
            route('payment.show', $booking)
        );

        return back()->with('success', 'Pengajuan disetujui. Customer diminta melakukan pembayaran.');
    }

    public function reject(
        RejectBookingRequest $request,
        Booking $booking,
        AvailabilityService $availability,
        NotificationService $notifications
    ): RedirectResponse {
        $reason = $request->validated()['alasan_penolakan'];

        DB::transaction(function () use ($booking, $reason, $availability) {
            $locked = Booking::query()->lockForUpdate()->findOrFail($booking->id);

            if ($locked->status !== Booking::STATUS_WAITING_CONFIRMATION) {
                throw ValidationException::withMessages([
                    'status' => 'Pengajuan ini tidak berada pada status menunggu konfirmasi.',
                ]);
            }

            $locked->update([
                'status' => Booking::STATUS_REJECTED,
                'rejection_reason' => $reason,
            ]);

            $availability->releaseForBooking($locked);
        });

        $booking->refresh()->loadMissing(['customer', 'tenant.user']);

        $notifications->send(
            $booking->customer,
            'Pengajuan Sewa Ditolak',
            "Pengajuan sewa {$booking->booking_code} ditolak. Alasan: {$reason}",
            route('orders.show', $booking)
        );

        return back()->with('success', 'Pengajuan ditolak dan customer telah diberi notifikasi.');
    }
}
