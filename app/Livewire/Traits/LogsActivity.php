<?php

namespace App\Livewire\Traits;

trait LogsActivity
{
    public function bootLogsActivity(): void
    {
        $component = class_basename($this);

        activity()
            ->inLog('page_visit')
            ->by(auth()->user())
            ->withProperties(['url' => $component])
            ->log("Opened a Livewire Component");
    }
}
