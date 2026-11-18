<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\CosplayInNotification;
use Illuminate\Support\Facades\Log;
use Throwable;

class NotificationService
{
    /**
     * Kanal notifikasi dipisahkan di kelas CosplayInNotification sehingga
     * SMS/WhatsApp dapat ditambahkan tanpa mengubah pemanggil.
     */
    public function send(?User $user, string $title, string $message, ?string $url = null, string $level = 'info'): void
    {
        if (! $user) {
            return;
        }

        try {
            $user->notify(new CosplayInNotification($title, $message, $url, $level));
        } catch (Throwable $exception) {
            // Email yang gagal tidak boleh membatalkan transaksi bisnis.
            Log::warning('Gagal mengirim notifikasi', [
                'user_id' => $user->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
