<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class AttendanceButton extends Component
{
    use AuthorizesRequests;

    public function render(): View
    {
        return view('livewire.attendance-button');
    }

    public function toggleAttendance(): void
    {
        $attendance = user()->getOrCreateActiveAttendance();

        if ($attendance->hasStarted()) {
            $attendance->checkout = now();
        } else {
            $attendance->checkin = now();
        }

        $attendance->save();

        $this->dispatch('attendanceButtonPressed');

        $this->dispatch('flashNotification', message: __('Attendance has been :action', [
            'action' => $attendance->hasStarted() ? __('started') : __('stopped'),
        ]));

        // if attendance is less than a minute, we delete the attendance
        if ($attendance->duration !== null && $attendance->duration < 1){
            $attendance->forceDelete();
        }
    }
}
