<?php

namespace App\Livewire\Calendars;

use App\Livewire\FullCalendar;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\User;
use GeneralHelper;

class AdminCalendar extends FullCalendar
{
    public function configure(): array
    {
        return [
            'resourceAreaHeaderContent' => __('Employee'),
            'initialView' => 'resourceTimelineMonth',
            'resourceAreaWidth' => '20%',
            'headerToolbar' => [
                'left' => 'today prev,next',
                'center' => 'title',
                'right' => 'resourceTimelineDay,resourceTimelineWeek,resourceTimelineMonth',
            ],
            'resources' => User::query()
                ->relevant()
                ->get()
                ->map(fn($user) => [
                    'id' => $user->id,
                    'title' => $user->name,
                ])
                ->toArray(),
        ];
    }

    public function eventSources(): array
    {
        return array(
            ['eventsCallback' => 'leaveEvents'],
            ['eventsCallback' => 'holidayEvents'],
            [
                'eventsCallback' => 'attendanceEvents',
                'options' => [
                    'color' => '#ffffff',
                    'className' => 'bg-primary',
                ],
            ],
        );
    }

    public function attendanceEvents($fetchInfo): array
    {
        $startDate = $fetchInfo['start'];
        $endDate = $fetchInfo['end'];

        $attendances = Attendance::whereBetween('checkin', [$startDate, $endDate])->get();

        return Attendance::toEventsArray($attendances);
    }

    public function leaveEvents($fetchInfo): array
    {
        $startDate = $fetchInfo['start'];
        $endDate = $fetchInfo['end'];

        $leaves = Leave::between($startDate, $endDate)->get();

        return Leave::toEventsArray($leaves);
    }

    public function holidayEvents(): array
    {
        return GeneralHelper::getHolidaysForCalendar();
    }
}
