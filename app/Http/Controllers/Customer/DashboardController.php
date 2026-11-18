<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $bookings = $user->bookings()
            ->with(['costume.images', 'tenant', 'latestPayment', 'returnItem'])
            ->latest()
            ->get();

        $activeOrders = $bookings->filter(fn (Booking $booking) => $booking->isActive());

        $paymentStatus = $bookings->filter(fn (Booking $booking) => in_array($booking->status, [
            Booking::STATUS_WAITING_PAYMENT,
            Booking::STATUS_PAID,
            Booking::STATUS_FAILED,
        ], true));

        $history = $bookings->filter(fn (Booking $booking) => in_array($booking->status, [
            Booking::STATUS_COMPLETED,
            Booking::STATUS_REJECTED,
        ], true));

        $stats = [
            'active' => $activeOrders->count(),
            'waiting_payment' => $bookings->where('status', Booking::STATUS_WAITING_PAYMENT)->count(),
            'completed' => $bookings->where('status', Booking::STATUS_COMPLETED)->count(),
            'total_spent' => $bookings->where('status', Booking::STATUS_COMPLETED)->sum('total'),
        ];

        return view('dashboard.index', compact('bookings', 'activeOrders', 'paymentStatus', 'history', 'stats'));
    }
}
