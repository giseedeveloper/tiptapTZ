<?php

namespace App\Notifications;

use App\Models\MenuEngagementSession;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CustomerMenuEngagementNotification extends Notification
{
    use Queueable;

    public function __construct(
        public MenuEngagementSession $session,
        public int $timeoutMinutes,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'menu_engagement',
            'session_id' => $this->session->id,
            'restaurant_id' => $this->session->restaurant_id,
            'table_id' => $this->session->table_id,
            'table_number' => $this->session->resolvedTableLabel(),
            'wa_id' => $this->session->wa_id,
            'menu_viewed_at' => $this->session->menu_viewed_at?->toIso8601String(),
            'timeout_minutes' => $this->timeoutMinutes,
            'message' => $this->session->alertMessage($this->timeoutMinutes),
        ];
    }
}
