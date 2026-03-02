<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class AttendanceAlert extends Component
{
    protected $listeners = ['attendanceButtonPressed' => '$refresh'];

    public function render(): View
    {
        return view('livewire.attendance-alert');
    }
}
