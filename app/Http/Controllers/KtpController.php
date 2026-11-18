<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KtpController extends Controller
{
    public function show(Booking $booking): StreamedResponse
    {
        $this->authorize('viewKtp', $booking);

        $disk = Storage::disk('local');

        abort_unless($booking->ktp_path && $disk->exists($booking->ktp_path), 404);

        return $disk->response($booking->ktp_path);
    }
}
