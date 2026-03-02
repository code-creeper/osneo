<?php

namespace App\Console\Commands;

use App\Enums\PayrollStatus;
use App\Models\Payroll;
use App\Models\User;
use App\Services\PayrollService;
use DB;
use Illuminate\Console\Command;

class CreatePayrollCommand extends Command
{
    protected $signature = 'payroll:create {date?}';

    protected $description = 'Create payroll of employees every month';

    public function handle(PayrollService $payrollService): void
    {
        $date = dateToCarbon($this->argument('date'), 'd-m-Y');

        if (!$date){
            $date = now()->previous('month')->startOfMonth();
        }

        $month = $date->format('Y-m');

        $users = User::relevant()->get();

        foreach ($users as $user){
            $employment = $user->getEmployment($date);

            if ( ! $employment) {
                continue;
            }

            $hourlyRate = $user->getHourlyRate($date);
            $targetHours = round($user->getDailyTargetTime($date) / 60, 2);

            $stats = $user->attendanceSummaries()
                ->whereRaw("DATE_FORMAT(date, '%Y-%m') = '$month'")
                ->select(
                    DB::raw('SUM(working_time + paid_time) as total_working_time'),
                    DB::raw('SUM(overtime) as total_overtime'),
                )
                ->first();

            $workingHours = round($stats->total_working_time / 60, 2);
            $overtimeHours = round($stats->total_overtime / 60, 2);
            $leaveBalance = $user->calculateLeaveBalance($date);

            $overtimes = array(
                [
                    'hourly_rate' => $hourlyRate,
                    'hours' => $overtimeHours
                ]
            );

            $daysWithoutAttendanceCount = $user->attendanceSummaries()
                ->where('leave', 0)
                ->where('off_day', 0)
                ->where('working_time', 0)
                ->whereRaw("DATE_FORMAT(date, '%Y-%m') = '$month'")->count();

            $forgottenLogoutsCount = $user->attendances()
                ->whereNull('checkout')
                ->whereRaw("DATE_FORMAT(date, '%Y-%m') = '$month'")
                ->count();

            $payroll = Payroll::updateOrCreate([
                'user_id' => $user->id,
                'date' => $date->toDateString(),
            ], [
                'hourly_rate' => $hourlyRate,
                'working_hours' => $workingHours,
                'target_hours' => $targetHours,
                'leaves_balance' => $leaveBalance,
                'overtimes' => $overtimes,
            ]);

            $payroll->status = ($forgottenLogoutsCount || $daysWithoutAttendanceCount)
                ? PayrollStatus::OPEN_ISSUES
                : PayrollStatus::READY;

            $payroll->vacation = $payrollService->getVacationDetails($payroll);
            $payroll->leaves = $payrollService->getLeavesByReason($payroll)->toArray();

            $payroll->save();

        }
    }
}
