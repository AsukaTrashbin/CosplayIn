<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewRequest;
use App\Models\Booking;
use App\Models\Review;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    public function store(
        ReviewRequest $request,
        Booking $booking,
        NotificationService $notifications
    ): RedirectResponse {
        $user = $request->user();

        $this->authorize('review', $booking);

        $target = $booking->reviewTargetFor($user);

        if (! $target) {
            throw ValidationException::withMessages([
                'rating' => 'Anda tidak dapat memberikan ulasan untuk pesanan ini.',
            ]);
        }

        $existing = Review::query()
            ->where('booking_id', $booking->id)
            ->where('reviewer_id', $user->id)
            ->exists();

        if ($existing) {
            throw ValidationException::withMessages([
                'rating' => 'Anda sudah memberikan ulasan untuk pesanan ini.',
            ]);
        }

        Review::query()->create([
            'booking_id' => $booking->id,
            'reviewer_id' => $user->id,
            'target' => $target,
            'rating' => (int) $request->validated()['rating'],
            'comment' => $request->validated()['comment'] ?? null,
        ]);

        $recipient = $target === Review::TARGET_TENANT
            ? $booking->tenant->user
            : $booking->customer;

        $notifications->send(
            $recipient,
            'Ulasan Baru',
            "Anda menerima ulasan baru untuk pesanan {$booking->booking_code}.",
            $target === Review::TARGET_TENANT
                ? route('tenant.dashboard')
                : route('orders.show', $booking)
        );

        return back()->with('success', 'Ulasan berhasil disimpan.');
    }
}
