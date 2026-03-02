<?php

namespace App\Livewire\Modals;

use App\Livewire\Traits\LogsActivity;
use App\Models\Announcement;
use Illuminate\Contracts\View\View;
use WireElements\Pro\Components\SlideOver\SlideOver;

class AnnouncementRecipientsModal extends SlideOver
{
    use LogsActivity;

    public Announcement|int $announcement;

    public function mount(Announcement $announcement): void
    {
        $this->announcement = $announcement;
    }

    public function render(): View
    {
        return view('livewire.modals.announcement-recipients');
    }

    public static function attributes(): array
    {
        return [
            'size' => 'xl',
        ];
    }

    public static function behavior(): array
    {
        return [
            'close-on-escape' => true,
            'close-on-backdrop-click' => true,
        ];
    }
}
