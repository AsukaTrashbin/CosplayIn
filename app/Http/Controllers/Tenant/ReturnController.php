<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\ReturnVerificationRequest;
use App\Models\Booking;
use App\Services\ReturnService;
use Illuminate\Http\RedirectResponse;

class ReturnController extends Controller
{
    public function verify(
        ReturnVerificationRequest $request,
        Booking $booking,
        ReturnService $returnService
    ): RedirectResponse {
        $this->authorize('manage', $booking);

        $return = $booking->returnItem;

        if (! $return) {
            return back()->withErrors(['status' => 'Customer belum mengajukan pengembalian.']);
        }

        $returnService->verify($return, $request->validated());

        return redirect()
            ->route('tenant.orders.show', $booking)
            ->with('success', 'Pengembalian diverifikasi. Pesanan selesai.');
    }
}
