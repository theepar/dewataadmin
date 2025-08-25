<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;

class NewBookingNotification extends Notification
{
    use Queueable;

    protected $event;

    /**
     * Create a new notification instance.
     */
    public function __construct($event)
    {
        $this->event = $event;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    /**
     * Get the FCM representation of the notification.
     */
    public function toFcm($notifiable)
    {
        $title = $this->event->title ?? 'Booking Baru';
        $body = $this->event->description ?? 'Ada booking baru atau data terupdate!';
        $updatedAt = $this->event->updated_at ? $this->event->updated_at->format('Y-m-d H:i:s') : null;

        return FcmMessage::create()
            ->setData([
                'type' => 'booking',
                'event_id' => $this->event->id ?? null,
                'updated_at' => $updatedAt, // Tanggal terakhir diupdate
            ])
            ->setNotification(
                \NotificationChannels\Fcm\Resources\Notification::create()
                    ->title($title)
                    ->body($body)
            );
    }
}
