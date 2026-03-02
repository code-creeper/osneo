<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\Employment;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Livewire\Component;


class EmploymentDetails extends Component
{
    public $model = Employment::class;
    public $label = 'employment';

    public $employment = '';
    public $employment_start = '';
    public $employment_end = '';
    private $employment_period_monthly = '';
    private $employment_period_daily = '';


    public $user = '';

    public function mount()
    {
       $this->employment = Employment::find(request()->employment);
       $this->employment_start = $this->employment->started_on;
       $this->employment_end = $this->employment->ended_on;
       if($this->employment_end === null) $this->employment_end = Carbon::now()->endOfMonth();
       $this->employment_period_monthly = CarbonPeriod::since($this->employment_start)->months()->until($this->employment_end);
       $this->employment_period_daily = CarbonPeriod::since($this->employment_start)->days()->until($this->employment_end);

       $this->user = User::find($this->employment->user_id);
    }

    public function render()
    {
        $data = [];

        $data['overview'] = $this->overview();
        $data['details'] = $this->details();

        return view('livewire.employment-details', $data);
    }

    public function overview()
    {
        $data = [];
        foreach($this->employment_period_monthly as $period)
        {
            $data[$period->format('Y')][$period->format('M')] =
                $this->overview_calculation($period) +
                [
                'grandTotal' => $this->overview_calculation($period, true)['total'],
                ];
        }
        return $data;
    }

    public function details()
    {
        $data = [];
        foreach($this->employment_period_daily as $period)
        {
            $data[$period->format('Y')][$period->format('F')][$period->format('d')] =
                $this->detail_calculation($period);
        }

        return $data;
    }

    private function overview_calculation($period, $grandTotal = false)
    {
        $startOfMonth = $period->startOfMonth()->format('Y-m-d');
        $endOfMonth = $period->endOfMonth()->format('Y-m-d');

        if($grandTotal) $startOfMonth = $this->employment_start->startOfMonth()->format('Y-m-d');

        $manualEntriesSum = DB::table('manual_entries')
            ->where('user_id', $this->employment->user_id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->where('payout', 0)
            ->selectRaw('SUM(duration) as sum')
            ->first()
            ->sum;
        $manualEntriesSum = $manualEntriesSum/60;

        $payoutsSum = DB::table('manual_entries')
            ->where('user_id', $this->employment->user_id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->where('payout', 1)
            ->selectRaw('SUM(duration) as sum')
            ->first()
            ->sum;
        $payoutsSum = $payoutsSum/60;

        $targetHoursSum = DB::table('attendance_summaries')
            ->where('user_id', $this->employment->user_id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('target_time');

        $workingHoursSum = DB::table('attendance_summaries')
            ->where('user_id', $this->employment->user_id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('working_time');

        return [
            'overtime' => $workingHoursSum + $manualEntriesSum - $targetHoursSum,
            'payouts' => $payoutsSum,
            'total' => $workingHoursSum + $manualEntriesSum - $targetHoursSum + $payoutsSum,
        ];
    }

    public function detail_calculation($date)
    {
        $day = $date->format('Y-m-d');

        $attendance = DB::table('attendance_summaries')
            ->where('user_id', $this->employment->user_id)
            ->where('date', $day)
            ->first(['target_time', 'working_time']);

        $target_hours = $attendance->target_hours ?? 0;
        $working_hours = $attendance->working_hours ?? 0;
        $overtime = $working_hours - $target_hours;

        $leaves = DB::table('leaves')
            ->join('leave_reasons', 'leaves.reason_id', '=', 'leave_reasons.id')
            ->where('leaves.user_id', $this->employment->user_id)
            ->whereDate('leaves.starts_on', '<=', $date)
            ->whereDate('leaves.ends_on', '>=', $date)
            ->select([
                'leaves.*',
                'leave_reasons.name as reason_name',
                'leave_reasons.color as reasons_color'
            ])
            ->first();

        $times = [];

        $attendances = Attendance::where('user_id', $this->employment->user_id)
            ->where('date', $day)
            ->get();

        foreach ($attendances as $attendance) {
            $attendanceId = $attendance->id;
            $duration = $attendance->duration;
            $entries = [];

            /*foreach ($attendance->entries as $entry) {
                $entryId = $entry->id;
                $loggedAt = $entry->logged_at;
                $type = $entry->type;
                $entries = [
                    'entryId' => $entryId,
                    'loggedAt' => $loggedAt,
                    'type' => $type,
                ];
            }*/

            $times[$attendanceId] = [
                'duration'  => $duration,
                'entries' => $entries,
            ];
        }

        return [
            'date' => $date,
            'target_hours' => $target_hours,
            'working_hours' => $working_hours,
            'overtime' => $overtime,
            'leaves' => $leaves,
            'times' => $times,

        ];
    }

    public function resetFilters()
    {

    }
}
