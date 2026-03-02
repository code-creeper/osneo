<?php

namespace App\Livewire\Widgets;

use App\Models\User;
use DB;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class PayrollWidget extends Component
{
    public string $payrollMonth = '';
    public $userId;
    public $shadow = true;
    public $employmentNotFound = false;

    public function mount(): void
    {
        $this->payrollMonth = $this->payrollMonth ?? now()->format('Y-m');
        $this->userId =  $this->userId ?? auth()->id();
    }

    public function render(): View
    {
        $data = array();

        $user = User::query()->with(['employments'])->find($this->userId);

        $date = Carbon::parse($this->payrollMonth)->startOfMonth();

        $employment = $user->getEmployment($date);

        $this->employmentNotFound = ! $employment->id;

        $attendanceSummariesStartDate = $employment?->started_on?->toDateString() ?? '2021-11-01';

        $data['payroll'] = DB::table('attendance_summaries')
            ->select(
                DB::raw('SUM(working_time + manual_time) AS attendance'),
                DB::raw('SUM(CASE WHEN `leave` = 1 THEN 1 ELSE 0 END) AS leaves'),
                DB::raw('SUM(CASE WHEN `leave` = 1 THEN paid_time ELSE 0 END) AS leaves_hours'),
                DB::raw('SUM(CASE WHEN `holiday` = 1 THEN 1 ELSE 0 END) AS holidays'),
                DB::raw('SUM(CASE WHEN `holiday` = 1 THEN paid_time ELSE 0 END) AS holiday_hours'),
                DB::raw('SUM(target_time) AS target_hours'),
                DB::raw('SUM(overtime) AS overtime'),
                DB::raw('SUM(payout_time) AS payout'),
                DB::raw("(
                    SELECT SUM(overtime) + SUM(payout_time) FROM attendance_summaries
                    WHERE DATE_FORMAT(`date`, '%Y-%m') <= '$this->payrollMonth'
                    and date >= '$attendanceSummariesStartDate' and user_id = $user->id
                ) AS total_balance"
                ),
                DB::raw('SUM(overtime) + SUM(payout_time) AS current_month_balance')
            )
            ->whereDateFormat('date', $this->payrollMonth, 'Y-m')
            ->where('user_id', '=', $user->id)
            ->first();

        return view('livewire.widgets.payroll', $data);
    }

}
