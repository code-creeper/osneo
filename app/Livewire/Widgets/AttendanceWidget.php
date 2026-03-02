<?php

namespace App\Livewire\Widgets;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class AttendanceWidget extends Component
{
    protected $listeners = [
        'attendanceButtonPressed' => '$refresh',
    ];

    public function mount(): void
    {
    }

    public function render(): View
    {
        $data = array();

        $user = User::query()
            ->with(['attendances' => fn($query) => $query->today(),])
            ->find(auth()->id());

        $data['user'] = $user;

        return view('livewire.widgets.attendance', $data);
    }

}
