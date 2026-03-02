<?php

namespace App\Livewire\Modals;

use App\Livewire\Traits\LogsActivity;
use App\Models\Activity;
use Illuminate\Contracts\View\View;
use WireElements\Pro\Components\Modal\Modal;

class ActivityDetails extends Modal
{
    use LogsActivity;

    public Activity|int $activity;
    public string $title;

    public function mount(Activity $activity): void
    {
        $this->activity = $activity;

        $this->title = class_basename($activity->subject) . ' ' . ucfirst($activity->event) ;
    }

    public function render(): View
    {
        return view('livewire.modals.activity-details');
    }

    public static function behavior(): array
    {
        return [
            'close-on-escape' => true,
            'close-on-backdrop-click' => true,
        ];
    }
}
