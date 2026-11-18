<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
use App\Models\Costume;
use App\Services\BookingService;
use App\Services\NotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Throwable;

class BookingController extends Controller
{
    public function create(Costume $costume): View
    {
        $costume->load(['tenant', 'images', 'sizes', 'category']);

        abort_unless($costume->is_active && $costume->tenant?->isActive(), 404);

        $sizes = $costume->sizes->keyBy('size');

        return view('booking.create', compact('costume', 'sizes'));
    }

    public function store(
        BookingRequest $request,
        BookingService $bookingService,
        NotificationService $notifications
    ): RedirectResponse {
        $data = $request->validated();

        $costume = Costume::query()->with('tenant')->findOrFail($data['costume_id']);

        abort_unless($costume->is_active && $costume->tenant?->isActive(), 404);

        $ktpPath = $request->file('ktp')->store('ktp', 'local');

        try {
            $booking = $bookingService->create($request->user(), $costume, [
                'ukuran' => $data['ukuran'],
                'nama_penyewa' => $data['nama_penyewa'],
                'nomor_identitas' => $data['nomor_identitas'],
                'telepon' => $data['telepon'],
                'alamat' => $data['alamat'],
                'tanggal_mulai' => $data['tanggal_mulai'],
                'tanggal_selesai' => $data['tanggal_selesai'],
                'metode_pengiriman' => $data['metode_pengiriman'],
                'ktp_path' => $ktpPath,
            ]);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($ktpPath);

            throw $exception;
        }

        $notifications->send(
            $booking->tenant->user,
            'Pengajuan Sewa Baru',
            "Ada pengajuan sewa baru {$booking->booking_code} untuk kostum {$costume->character_name}. Silakan konfirmasi.",
            route('tenant.bookings.index')
        );

        return redirect()
            ->route('orders.show', $booking)
            ->with('success', 'Pengajuan sewa berhasil dikirim. Menunggu konfirmasi tenant.');
    }
}
