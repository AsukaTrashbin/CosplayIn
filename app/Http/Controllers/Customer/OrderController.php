<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function show(Request $request, Booking $booking): View
    {
        $this->authorize('view', $booking);

        $booking->load([
            'costume.images',
            'tenant.user',
            'payments',
            'returnItem',
            'reviews.reviewer',
        ]);

        $user = $request->user();
        $canReview = $booking->canBeReviewedBy($user);
        $reviewTarget = $booking->reviewTargetFor($user);

        return view('pesanan.show', compact('booking', 'canReview', 'reviewTarget'));
    }
}
