<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReturnRequest;
use App\Models\Booking;
use App\Services\ReturnService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ReturnController extends Controller
{
    public function create(Booking $booking): View|RedirectResponse
    {
        $this->authorize('view', $booking);

        if (! $booking->canStartReturn()) {
            return redirect()
                ->route('orders.show', $booking)
                ->with('error', 'Pengembalian belum dapat dimulai. Pastikan masa sewa telah berakhir dan pesanan sudah dibayar.');
        }

        $booking->load(['costume.images', 'returnItem']);

        return view('pengembalian.create', compact('booking'));
    }

    public function store(ReturnRequest $request, Booking $booking, ReturnService $returnService): RedirectResponse
    {
        $this->authorize('view', $booking);

        $returnService->start($booking, $request->validated());

        return redirect()
            ->route('orders.show', $booking)
            ->with('success', 'Pengembalian berhasil diajukan. Menunggu verifikasi tenant.');
    }
}
