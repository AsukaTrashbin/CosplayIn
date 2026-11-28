<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Contracts\View\View;

class OrderController extends Controller
{
    public function show(Booking $booking): View
    {
        $this->authorize('view', $booking);

        $booking->load([
            'costume.category',
            'costume.tenant',
            'costume.images',
        ]);

        return view('pesanan.show', compact('booking'));
    }
}
