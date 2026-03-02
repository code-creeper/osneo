<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Notification;

class UserUpdated extends Notification
{

    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function via(): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->user->id,
            'message' => $this->getMessage(),
            'icon' => 'fal fa-bell'
        ];
    }

    public function getMessage(): string
    {
        return __(
            'An admin has <strong>updated</strong> your information'
        );
    }
}
