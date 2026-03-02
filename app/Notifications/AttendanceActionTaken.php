<?php

namespace App\Notifications;

use App\Enums\AttendanceAction;
use App\Models\Attendance;
use Illuminate\Notifications\Notification;

class AttendanceActionTaken extends Notification
{

    private Attendance $attendance;
    private AttendanceAction $action;

    public function __construct(Attendance $attendance, AttendanceAction $action)
    {
        $this->attendance = $attendance;
        $this->action = $action;
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
            'icon' => 'fal fa-bell'
        ];
    }

    public function getMessage(): string
    {
        return __(
            'An admin has <strong>:action</strong> your attendance',
            ['action' => __($this->action->name)]
        );
    }
}
