<?php

namespace App\Notifications;

use App\Models\ManualEntry;
use Illuminate\Notifications\Notification;

class ManualEntryNotification extends Notification
{

    private ManualEntry $entry;

    public function __construct(ManualEntry $entry)
    {
        $this->entry = $entry;
    }

    public function via(): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(): array
    {
        return [
            'entry_id' => $this->entry->id,
            'message' => $this->getMessage(),
            'icon' => 'fal fa-bell'
        ];
    }

    public function getMessage(): string
    {
        return __(
            'An admin has <strong>:action</strong> :duration :to/from your working hours :as_payout',
            [
                'action' => $this->entry->isBreak() ? __("Removed") : __("Added"),
                'to/from' => $this->entry->isBreak() ? __('from') : __('to'),
                'duration' => formatMins($this->entry->duration, true),
                'as_payout' => $this->entry->payout ? __('as payout') : ''
            ]
        );
    }
}
