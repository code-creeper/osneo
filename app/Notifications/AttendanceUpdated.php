<?php

namespace App\Notifications;

use App\Models\Attendance;
use Illuminate\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class AttendanceUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    private Attendance $attendance;

    public function __construct(Attendance $attendance)
    {
        $this->attendance = $attendance;
    }

    public function via(): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(): array
    {
        return [
            'attendance_id' => $this->attendance->id,
            'message' => $this->getMessage(),
            'icon' => 'fal fa-file-check'
        ];
    }

    public function getMessage(): string
    {
        return __('Your attendance has been checked out by <strong>System</strong>');
    }
}
