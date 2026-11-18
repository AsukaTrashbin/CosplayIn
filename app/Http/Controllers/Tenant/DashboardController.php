<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Review;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = $request->user()->tenant;

        $totalUsers = Booking::query()
            ->where('tenant_id', $tenant->id)
            ->distinct('customer_id')
            ->count('customer_id');

        $totalCostumes = $tenant->costumes()->count();

        $revenue = Payment::query()
            ->where('status', Payment::STATUS_SUCCESS)
            ->whereHas('booking', fn ($q) => $q->where('tenant_id', $tenant->id))
            ->sum('amount');

        $commissionPercent = (float) Setting::get('platform_commission_percent', config('cosplayin.commission_percent', 10));
        $earnings = (int) round($revenue * (1 - ($commissionPercent / 100)));

        $averageRating = Review::query()
            ->where('target', Review::TARGET_TENANT)
            ->whereHas('booking', fn ($q) => $q->where('tenant_id', $tenant->id))
            ->avg('rating');

        $pendingRequests = Booking::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', Booking::STATUS_WAITING_CONFIRMATION)
            ->count();

        $recentBookings = Booking::query()
            ->where('tenant_id', $tenant->id)
            ->with(['customer', 'costume'])
            ->latest()
            ->take(8)
            ->get();

        return view('tenant.dashboard', [
            'tenant' => $tenant,
            'totalUsers' => $totalUsers,
            'totalCostumes' => $totalCostumes,
            'revenue' => (int) $revenue,
            'earnings' => $earnings,
            'averageRating' => $averageRating ? round((float) $averageRating, 1) : 0,
            'pendingRequests' => $pendingRequests,
            'recentBookings' => $recentBookings,
            'commissionPercent' => $commissionPercent,
        ]);
    }
}
