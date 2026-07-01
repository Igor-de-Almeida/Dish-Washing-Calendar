<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class TodayIsYourTurn extends Notification
{
    use Queueable;

    public $schedule;

    public function __construct($schedule)
    {
        $this->schedule = $schedule;
    }

    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush(object $notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title('🧼 Hoje é o teu dia!')
            ->body('Lembra-te de lavar a loiça hoje (' . $this->schedule->scheduled_date . ')')
            ->icon(asset('images/favicon.ico'))  // opcional
            ->action('Ver Calendário', route('calendar'))
            ->data(['url' => route('calendar')]);
    }
}