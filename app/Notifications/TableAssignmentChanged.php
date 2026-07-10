<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TableAssignmentChanged extends Notification
{
    use Queueable;

    /**
     * @param  list<string>  $tableNames
     */
    public function __construct(
        public string $message,
        public array $tableNames = [],
        public ?string $assignedBy = null,
    ) {}

    /**
     * @return array<int, string>
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
            'type' => 'table_assignment_changed',
            'message' => $this->message,
            'table_names' => $this->tableNames,
            'assigned_by' => $this->assignedBy,
            'url' => route('waiter.dashboard'),
        ];
    }
}
