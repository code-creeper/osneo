<?php

namespace App\Livewire\Modals;

use App\Livewire\Traits\LogsActivity;
use App\Models\Announcement;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use WireElements\Pro\Components\Modal\Modal;

class AnnouncementModal extends Modal
{
    use LogsActivity;

    public Announcement|int $announcement;

    public Collection $announcements;

    public bool $preview = false;

    public int $index = 0;
    public int $total = 0;

    public function mount(Announcement $announcement): void
    {
        $this->announcement = $announcement;

        if ($announcement->id) {
            $this->preview = true;
        } else {
            $this->initialize();
        }
    }

    public function initialize(): void
    {
        if ($this->preview){
            return;
        }

        $this->announcements = user()->unreadAnnouncements()->get();
        $this->total = $this->announcements->count();

        // if there is no unread announcement, close the modal
        if ($this->total == 0){
            $this->close();
            return;
        }

        // if the current item is the last item, we move it one step back
        if ($this->index >= $this->total){
            $this->index--;
        }

        $this->announcement = $this->announcements[$this->index];
    }

    public function render(): View
    {
        return view('livewire.modals.announcement');
    }

    public function next(): void
    {
        if ($this->preview){
            return;
        }

        // if current is more than total, return
        if (($this->index + 1) >= $this->total){
            return;
        }

        if ($this->index < $this->total) {
            $this->index++;
        }

        $this->announcement = $this->announcements[$this->index];
    }

    public function previous(): void
    {
        if ($this->preview){
            return;
        }

        if (($this->index - 1) < 0){
            return;
        }

        $this->index--;
        $this->announcement = $this->announcements[$this->index];
    }

    public function markAsRead(): void
    {
        if ($this->preview){
            return;
        }

        user()->unreadAnnouncements()->updateExistingPivot($this->announcement->id, [
            'read_at' => now(),
        ]);

        $this->initialize();
    }

    public function markAllAsRead(): void
    {
        if ($this->preview){
            return;
        }

        if ($this->total < 1) {
            return;
        }

        user()->unreadAnnouncements()->updateExistingPivot($this->announcements->pluck('id'), [
            'read_at' => now(),
        ]);

        $this->close();
    }

    public static function attributes(): array
    {
        return [
            'size' => '3xl'
        ];
    }
}
