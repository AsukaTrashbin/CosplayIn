<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $bookings = $this->customerBookings($request->user())->get();

        $activeOrder = $bookings->first(fn (Booking $booking) => $booking->isActive());

        $history = $bookings
            ->filter(fn (Booking $booking) => $this->isHistory($booking))
            ->take(3)
            ->values();

        return view('dashboard.index', [
            'activeOrder' => $activeOrder,
            'paymentOrder' => $activeOrder,
            'history' => $history,
        ]);
    }

    public function activeOrders(Request $request): View
    {
        $activeOrders = $this->customerBookings($request->user())
            ->whereIn('status', $this->activeStatuses())
            ->latest()
            ->get();

        return view('dashboard.orders-active', compact('activeOrders'));
    }

    public function payments(Request $request): View
    {
        $paymentOrders = $this->customerBookings($request->user())
            ->whereIn('status', $this->paymentStatuses())
            ->latest()
            ->get();

        $statusFilters = collect(['semua' => 'Semua'])
            ->merge(
                $paymentOrders
                    ->pluck('status')
                    ->unique()
                    ->mapWithKeys(fn (string $status) => [
                        $status => $paymentOrders->firstWhere('status', $status)->paymentStatusLabel(),
                    ])
            );

        $activeFilter = $request->query('status', 'semua');

        if ($activeFilter !== 'semua' && $statusFilters->has($activeFilter)) {
            $paymentOrders = $paymentOrders->where('status', $activeFilter)->values();
        } else {
            $activeFilter = 'semua';
        }

        return view('dashboard.payments', compact('paymentOrders', 'statusFilters', 'activeFilter'));
    }

    public function rentalHistory(Request $request): View
    {
        $history = $this->customerBookings($request->user())
            ->whereIn('status', $this->historyStatuses())
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('dashboard.rentals-history', compact('history'));
    }

    /**
     * @return HasMany<Booking, User>
     */
    private function customerBookings(User $user): HasMany
    {
        return $user->bookings()
            ->with(['costume.images', 'tenant', 'latestPayment'])
            ->latest();
    }

    /**
     * @return array<int, string>
     */
    private function activeStatuses(): array
    {
        return [
            Booking::STATUS_WAITING_CONFIRMATION,
            Booking::STATUS_WAITING_PAYMENT,
            Booking::STATUS_PAID,
            Booking::STATUS_READY_TO_PROCESS,
            Booking::STATUS_RENTED,
            Booking::STATUS_RETURNED,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function paymentStatuses(): array
    {
        return [
            Booking::STATUS_WAITING_PAYMENT,
            Booking::STATUS_PAID,
            Booking::STATUS_FAILED,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function historyStatuses(): array
    {
        return [
            Booking::STATUS_COMPLETED,
            Booking::STATUS_REJECTED,
        ];
    }

    private function isHistory(Booking $booking): bool
    {
        return in_array($booking->status, $this->historyStatuses(), true);
    }
}
