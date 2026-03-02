<?php

namespace App\Notifications;

use App\Enums\LeaveAction;
use App\Models\Leave;
use Illuminate\Notifications\Notification;

class LeaveActionTaken extends Notification
{
    private Leave $leave;
    private LeaveAction $action;

    public function __construct(Leave $leave, LeaveAction $action)
    {
        $this->leave = $leave;
        $this->action = $action;
    }

    public function via(): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(): array
    {
        return [
            'leave_id' => $this->leave->id,
            'message' => $this->getMessage(),
            'icon' => 'fal fa-bell'
        ];
    }

    public function getMessage(): string
    {
        return __(
            'An admin has <strong>:action</strong> your leave',
            ['action' => __($this->action->name)]
        );
    }
}
