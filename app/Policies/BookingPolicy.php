<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function view(User $user, Booking $booking): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($booking->customer_id === $user->id) {
            return true;
        }

        return $user->isTenant()
            && $user->tenant
            && $booking->tenant_id === $user->tenant->id;
    }

    public function viewKtp(User $user, Booking $booking): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isTenant()
            && $user->tenant
            && $booking->tenant_id === $user->tenant->id;
    }

    public function manage(User $user, Booking $booking): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isTenant()
            && $user->tenant
            && $booking->tenant_id === $user->tenant->id;
    }

    public function review(User $user, Booking $booking): bool
    {
        if ($booking->status !== Booking::STATUS_COMPLETED) {
            return false;
        }

        return $booking->customer_id === $user->id
            || ($user->tenant && $booking->tenant_id === $user->tenant->id);
    }
}
