<?php

namespace App\Notifications;

use App\Models\Leave;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeaveRequested extends Notification
{
    use Queueable;

    public function __construct(public Leave $leave)
    {
    }

    /**
     * Deliver via the database channel (in-app notifications).
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Payload stored in the notifications.data JSON column.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $applicant = $this->leave->employee->user->name ?? 'An employee';

        return [
            'type' => 'leave_requested',
            'leave_id' => $this->leave->id,
            'message' => "{$applicant} requested {$this->leave->leave_type} leave.",
            'url' => route('leaves.manage'),
        ];
    }
}
