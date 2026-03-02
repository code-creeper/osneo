<?php

namespace App\Notifications;

use App\Models\Attendance;
use App\Models\Modification;
use Illuminate\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class ChangesApproved extends Notification implements ShouldQueue
{
    use Queueable;

    private Modification $modification;

    public function __construct(Modification $modification)
    {
        $this->modification = $modification;
    }

    public function via(): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(): array
    {
        return [
            'modification_id' => $this->modification->id,
            'message' => $this->getMessage(),
            'icon' => 'fal fa-check-circle'
        ];
    }

    public function getMessage(): string
    {
        $action = __($this->modification->type->value);

        $modifiable = class_basename($this->modification->modifiable_type);

        return __(
            'An admin has approved your request to <strong>:action</strong> your :modifiable',
            ['action' => $action, 'modifiable' => $modifiable]
        );
    }
}
