<?php

namespace App\Notifications;

use App\Models\Leave;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeaveProcessed extends Notification
{
    use Queueable;

    public function __construct(public Leave $leave)
    {
    }

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
            'type' => 'leave_processed',
            'leave_id' => $this->leave->id,
            'status' => $this->leave->status,
            'message' => "Your {$this->leave->leave_type} leave was {$this->leave->status}.",
            'url' => route('leaves.show', $this->leave->id),
        ];
    }
}
