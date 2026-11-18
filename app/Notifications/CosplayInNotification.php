<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CosplayInNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public ?string $url = null,
        public string $level = 'info',
    ) {
    }

    /**
     * TODO: Kanal final belum ditentukan. Saat ini database (in-app) dan email.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $allowed = ['database', 'mail'];
        $channels = config('cosplayin.notifications.channels', $allowed);

        return array_values(array_intersect($channels, $allowed));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'level' => $this->level,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('CosplayIn - '.$this->title)
            ->greeting('Halo '.$notifiable->name.',')
            ->line($this->message)
            ->action('Buka CosplayIn', $this->url ? url($this->url) : url('/'))
            ->line('Terima kasih telah menggunakan CosplayIn.');
    }
}
