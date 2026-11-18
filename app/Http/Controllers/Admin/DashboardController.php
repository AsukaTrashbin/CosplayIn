<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Costume;
use App\Models\Payment;
use App\Models\Review;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalUsers = User::query()->count();
        $totalCostumes = Costume::query()->count();
        $totalTenants = Tenant::query()->where('status', Tenant::STATUS_ACTIVE)->count();

        $revenue = (int) Payment::query()->where('status', Payment::STATUS_SUCCESS)->sum('amount');

        $commissionPercent = (float) Setting::get('platform_commission_percent', config('cosplayin.commission_percent', 10));
        $earnings = (int) round($revenue * ($commissionPercent / 100));

        $averageRating = Review::query()->avg('rating');

        $pendingTenants = Tenant::query()
            ->where('status', Tenant::STATUS_PENDING)
            ->with('user')
            ->latest()
            ->get();

        $recentBookings = Booking::query()
            ->with(['customer', 'tenant', 'costume', 'latestPayment'])
            ->latest()
            ->take(10)
            ->get();

        $bookingsByStatus = Booking::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $monthlyRevenue = Booking::query()
            ->whereIn('status', [Booking::STATUS_COMPLETED, Booking::STATUS_PAID, Booking::STATUS_READY_TO_PROCESS, Booking::STATUS_RENTED, Booking::STATUS_RETURNED])
            ->get()
            ->groupBy(fn (Booking $booking) => Carbon::parse($booking->created_at)->format('Y-m'))
            ->map(fn ($items, $month) => [
                'month' => $month,
                'total' => (int) $items->sum('total'),
                'orders' => $items->count(),
            ])
            ->sortKeysDesc()
            ->take(6);

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalCostumes',
            'totalTenants',
            'revenue',
            'earnings',
            'averageRating',
            'commissionPercent',
            'pendingTenants',
            'recentBookings',
            'bookingsByStatus',
            'monthlyRevenue'
        ));
    }
}
