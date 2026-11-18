<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = $request->user()->tenant;

        $completed = Booking::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', Booking::STATUS_COMPLETED)
            ->with('costume')
            ->latest()
            ->get();

        $monthly = $completed
            ->groupBy(fn (Booking $booking) => Carbon::parse($booking->created_at)->format('Y-m'))
            ->map(function ($items, $month) {
                return [
                    'month' => $month,
                    'orders' => $items->count(),
                    'gross' => (int) $items->sum('total'),
                    'late_fees' => (int) $items->sum(fn (Booking $booking) => $booking->returnItem?->late_fee ?? 0),
                ];
            })
            ->sortKeysDesc();

        $commissionPercent = (float) Setting::get('platform_commission_percent', config('cosplayin.commission_percent', 10));
        $gross = (int) $completed->sum('total');
        $commission = (int) round($gross * ($commissionPercent / 100));

        return view('tenant.laporan', [
            'monthly' => $monthly,
            'gross' => $gross,
            'commission' => $commission,
            'earnings' => $gross - $commission,
            'commissionPercent' => $commissionPercent,
            'completedCount' => $completed->count(),
            'completed' => $completed->take(20),
        ]);
    }
}
