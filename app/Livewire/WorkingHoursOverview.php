<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\User;
use Livewire\Component;

class WorkingHoursOverview extends Component
{
    //TODO:: remove - deprecated. now we use payroll module
    protected $listeners = [
        'updateWorkingHoursOverview' => 'render',
    ];

    public $model = Attendance::class;
    public $label = 'attendance';
    public $user = '';
    public $date = '';
    public $month = '';
    public $year = '';
    public $selectedUser = '';

    public $search = '';
    public $sortBy = 'id';
    public $sortOrder = 'desc';
    public $perPageOptions = ['25', '50', '100'];
    public $perPage = 25;

    public function mount()
    {
        $this->date = now()->format('Y-m');
        $this->month = now()->month;
        $this->year = now()->year;
    }

    public function render()
    {
        $data = array();

        $data['heading'] = __('Working Hours Overview');

        $whereMonthOfYear = fn($query) => $query->whereMonthOfYear(
            'date', $this->month, $this->year
        );

        $wherePayOut = fn($query) => $query
            ->whereMonthOfYear('date', $this->month, $this->year)
            ->where('payout', 1);

        $fnLeaveCount = fn($query) => $query->whereMonthOfYear('date', $this->month, $this->year)
            ->withWhereHas('leave', fn($query) => $query->approved());

        $rows = User::query()
            ->withSum([
                'manualBreaks as total_manual_break',
                'manualBreaks as manual_break' => $whereMonthOfYear,
                'manualBreaks as payouts' => $wherePayOut,

                'manualAttendances as total_manual_attendance',
                'manualAttendances as manual_attendance' => $whereMonthOfYear,
                'manualAttendances as payouts_deductions' => $wherePayOut,
            ], 'duration')
            ->withSum([
                'attendanceSummaries as payout_time' => $whereMonthOfYear,
            ], 'payout_time')
            ->withSum([
                'attendanceSummaries as total_working_hours',
                'attendanceSummaries as working_hours' => $whereMonthOfYear,
            ], 'working_time')
            ->withSum([
                'attendanceSummaries as total_target_hours',
                'attendanceSummaries as target_hours' => $whereMonthOfYear,
            ], 'target_time')
            ->withCount([
                'sickLeaveDays as sick_leaves_count' => $fnLeaveCount,
                'childSickLeaveDays as child_sick_leaves_count' => $fnLeaveCount,
                'paidLeaveDays as paid_leaves_count' => $fnLeaveCount,
            ])
            ->when(
                $this->selectedUser,
                fn($query) => $query->whereId($this->selectedUser)
            )
            ->when(
                $this->search,
                fn($query) => $query->whereAnyColumnLike($this->search)
            )
            ->when(
                $this->user,
                fn($query) => $query->whereUserId($this->user)
            )
            ->sort($this->sortBy, $this->sortOrder)
            ->paginate($this->perPage);

        $rows->getCollection()->transform(function ($user) {
            $user->working_hours += $this->getManualEntriesHours($user) + $this->getPayouts($user);
            $user->overtime = $user->working_hours - $user->target_hours;
            $user->total_overtime = $user->total_working_hours + $this->getTotalManualHours($user) - $user->total_target_hours;

            $user->monthly_target_hours = $user->getMonthlyTargetHours($this->year, $this->month);
            $user->creditable_hours = $user->getMonthlyTargetHours($this->year, $this->month) + $user->overtime;

            $user->payout = $this->getPayouts($user);

            return $user;
        });

        $data['users'] = $rows;
        $data['label'] = 'user';

        return view('livewire.working-hours-overview', $data);
    }

    public function getManualEntriesHours($user)
    {
        $manual_entries_duration = $user->manual_attendance - $user->manual_break;

        return round($manual_entries_duration / 60, 2);
    }

    public function getPayouts($user)
    {
        return round($user->payouts / 60, 2) - round($user->payouts_deductions / 60, 2);
    }

    public function getTotalManualHours($user)
    {
        $total_manual_entries_duration = $user->total_manual_attendance - $user->total_manual_break;

        return round($total_manual_entries_duration / 60, 2);
    }

    public function resetFilters()
    {
        $this->date = now()->format('Y-m');
        $this->month = now()->month;
        $this->year = now()->year;
        $this->reset(['selectedUser']);
    }

    public function onChangeDate()
    {
        if ( ! $this->date) {
            return false;
        }

        $date = explode('-', $this->date);
        $this->year = $date[0];
        $this->month = $date[1];
    }
}
