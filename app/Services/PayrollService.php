<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceSummary;
use App\Models\LeaveDay;
use App\Models\Payroll;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class PayrollService
{
    public function getLeavesByReason(Payroll $payroll): Collection
    {
        return DB::table('leave_days as ld')
            ->select('r.name')
            ->selectRaw("SUM(CASE WHEN DATE_FORMAT(ld.date, '%Y-%m') = '$payroll->month' THEN 1 ELSE 0 END) as leaves_this_month")
            ->selectRaw("SUM(CASE WHEN YEAR(ld.date) = '$payroll->year' THEN 1 ELSE 0 END) as leaves_this_year")
            ->join('leave_reasons as r', 'ld.reason_id', '=', 'r.id')
            ->where('ld.user_id', $payroll->user_id)
            ->groupBy('r.name')
            ->havingRaw('leaves_this_month > 0 OR leaves_this_year > 0')
            ->get();
    }

    public function getVacationDetails(Payroll $payroll): array
    {
        $leavesTaken = DB::table('leave_days AS ld')
            ->select([
                DB::raw('SUM(CASE WHEN l.approved_at IS NOT NULL AND ld.date <= CURDATE() THEN 1 ELSE 0 END) AS leaves_taken'),
            ])
            ->join('leaves AS l', 'ld.leave_id', '=', 'l.id')
            ->join('leave_reasons', 'ld.reason_id', '=', 'leave_reasons.id')
            ->where('l.user_id', $payroll->user_id)
            ->whereYear('ld.date', $payroll->year)
            ->where('leave_reasons.deductible', 1)
            ->first();

        return array(
            'this_year' => $payroll->user->getAnnualLeaveEntitlement($payroll->year),
            'last_year' => $payroll->user->calculateLeaveBalance($payroll->date->previous('year')),
            'taken' => $leavesTaken->leaves_taken,
            'current_balance' => $payroll->user->calculateLeaveBalance($payroll->year),
        );
    }

    public function getMissingTimes(Payroll $payroll): Collection
    {
        $leaveDays = $payroll->user->leaveDays()->whereDateFormat('date', $payroll->month, 'Y-m')
            ->pluck('date');

        $missingTimesDates = $payroll->user->attendanceSummaries()
            ->where('leave', 0)
            ->where('off_day', 0)
            ->where('working_time', 0)
            ->where('holiday', 0)
            ->whereDateFormat('date', $payroll->month, 'Y-m')
            ->pluck('date');

        return $missingTimesDates->diff($leaveDays);
    }

    public function getForgottenLogouts(Payroll $payroll): Collection
    {
        return $payroll->user->attendances()
            ->whereNull('checkout')
            ->whereRaw("DATE_FORMAT(checkout, '%H-%i') = '23:59'")
            ->get();
    }

    public function getAbnormalAttendance(Payroll $payroll): Collection
    {
        return AttendanceSummary::query()
            ->select(
                '*',
                DB::raw("CASE WHEN off_day = 1 THEN 'off day' ELSE 'leave' END AS anomaly")
            )
            ->where('working_time', '>', 0)
            ->where(function (Builder $query) {
                $query->where('off_day', true)->orWhere('leave', true);
            })
            ->whereRaw("DATE_FORMAT(date, '%Y-%m') = '$payroll->month'")
            ->where("user_id", $payroll->user_id)
            ->get();
    }

    // attendances which are updated in last month but are not created in the last month
    public function getUpdatedAttendances(Payroll $payroll): Collection
    {
        return $payroll->user
            ->attendances()
            ->whereDate('created_at', '<', $payroll->date->startOfMonth())
            ->whereRaw("DATE_FORMAT(updated_at, '%Y-%m') = '$payroll->month'")
            ->get();
    }

    // leaves which are updated in last month but are not created in the last month
    public function getUpdatedLeaves(Payroll $payroll): Collection
    {
        return $payroll->user
            ->leaves()
            ->whereDate('created_at', '<', $payroll->date->startOfMonth())
            ->whereRaw("DATE_FORMAT(updated_at, '%Y-%m') = '$payroll->month'")
            ->get();
    }
}
