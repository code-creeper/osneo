<?php

namespace App\Livewire\Calendars;

use App\Livewire\FullCalendar;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\User;
use GeneralHelper;

class EmployeeCalendar extends FullCalendar
{
    public function configure(): array
    {
        return [
            'eventMaxStack' => 3,
        ];
    }

    public function eventSources(): array
    {
        return [
            [
                'eventsCallback' => 'attendanceEvents',
                'options' => [
                    'color' => '#ffffff',
                    'className' => 'bg-primary',
                ],
            ],
            ['eventsCallback' => 'leaveEvents'],
            ['eventsCallback' => 'holidayEvents'],
        ];
    }

    public function attendanceEvents($fetchInfo): array
    {
        $startDate = $fetchInfo['start'];
        $endDate = $fetchInfo['end'];

        $attendances = Attendance::query()
            ->whereUserId(user()->id)
            ->checkedOut()
            ->whereBetween('checkin', [$startDate, $endDate])
            ->get();

        return Attendance::toEventsArray($attendances);
    }

    public function leaveEvents($fetchInfo): array
    {
        $startDate = $fetchInfo['start'];
        $endDate = $fetchInfo['end'];

        $leaves = Leave::query()
            ->whereUserId(user()->id)
            ->between($startDate, $endDate)
            ->get();

        return Leave::toEventsArray($leaves);
    }

    public function holidayEvents(): array
    {
        return GeneralHelper::getHolidaysForCalendar();
    }
}
