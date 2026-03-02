<?php


namespace App\Traits\Employee;

use App\Models\Leave;
use App\Models\LeaveDay;
use App\Models\LeaveTransaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;

trait HasLeave
{
    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function leaveTransactions()
    {
        return $this->hasMany(LeaveTransaction::class);
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    public function futureLeaves()
    {
        return $this->leaves()->future('starts_on');
    }

    public function ongoingLeaves()
    {
        return $this->leaves()->ongoing();
    }

    public function paidLeaves()
    {
        return $this->leaves()->paid();
    }

    public function deductibleLeaves()
    {
        return $this->leaves()->deductible();
    }

    public function sickLeaves()
    {
        return $this->leaves()->type('sick_leave');
    }

    public function childSickLeaves()
    {
        return $this->leaves()->type('child_sick_leave');
    }

    //todo:: remove - offsite leaves are replaces by paid/unpaid
    /*public function offsiteLeaves()
    {
        return $this->leaves()->offsiteWork();
    }*/

    public function leaveDays()
    {
        return $this->hasMany(LeaveDay::class);
    }

    public function paidLeaveDays()
    {
        return $this->leaveDays()->paid();
    }

    public function sickLeaveDays()
    {
        return $this->leaveDays()->type('sick_leave');
    }

    public function childSickLeaveDays()
    {
        return $this->leaveDays()->type('child_sick_leave');
    }

    //todo:: remove - offsite leaves are replaces by paid/unpaid
    /*public function offsiteLeaveDays()
    {
        return $this->leaveDays()->offsiteWork();
    }*/


    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    public function onLeave(Carbon $date = null, $paid = null): bool
    {
        //todo::question: if leave is not approved yet, should we return true?
        $date = $date ?? today();

        /*return LeaveDay::joinRelationship('leave.reason', [
            'leave' => fn($leave) => $leave->whereNotNull('approved_by'),
            'reason' => fn($reason) => $reason->when($paid !== null,
                fn($reason) => $reason->where('paid', $paid)
            ),
        ])
            ->whereDate('date', $date->toDateString())
            ->where('leave_days.user_id', $this->id)
            ->count();*/

        return LeaveDay::query()
            ->whereHas('leave', fn(Builder $query) => $query->approved())
            ->when($paid !== null, fn(Builder $query) => $query->whereRelation('reason', 'paid', $paid))
            ->whereDate('date', $date->toDateString())
            ->where('user_id', $this->id)
            ->count();
    }

    public function onPaidLeave(Carbon $date = null): bool
    {
        return $this->onLeave($date, 1);
    }

    public function onUnPaidLeave(Carbon $date = null): bool
    {
        return $this->onLeave($date, 0);
    }

    /**
     * @throws \Throwable
     */
    public function createLeaveTransaction($data)
    {
        throw_if(!($data['amount'] ?? null), new Exception('Amount is required and should be non zero'));

        return $this->leaveTransactions()->create([
            'transacted_by' => $data['transacted_by'] ?? null,
            'transacted_on' => $data['transacted_on'] ?? now(),
            'amount' => $data['amount'],
            'comments' => $data['comments'] ?? null,
        ]);
    }

    /**
     * Calculates Leave entitlement for given year
     *
     * @param  ?string  $year
     *
     * @return float
     */
    public function getAnnualLeaveEntitlement(string $year = null): float
    {
        $year = $year ? now()->year($year) : now();
        return $this->getLeaveEntitlement($year->copy()->startOfYear(), $year->copy()->endOfYear());
    }

    /**
     * Calculates Leave entitlement for given period
     * if $getCurrentBalance is set to true, we return the leave balance for the given period
     *
     * @param  ?string|Carbon  $startDate
     * @param  ?string|Carbon  $endDate
     * @param  bool  $getCurrentBalance
     *
     * @return float
     */
    public function getLeaveEntitlement(Carbon|string $startDate = null, Carbon|string $endDate = null, bool $getCurrentBalance = false): float
    {
        $endDate = ($endDate == null) ? now() : Carbon::parse($endDate);

        try {
            $employment = $this->getEmployment($endDate, true);
        } catch (\Throwable $exception) {
            return 0;
        }

        $startDate = ($startDate == null) ? $employment->started_on : Carbon::parse($startDate);

        // Year, upto which we calculate the leaves entitlement
        $year = $endDate->year;

        // if the employment started in the provided year ( year of $startDate),
        // then we set the $startDate to the employment's start date; otherwise
        // we set the $startDate to the start of year
        $startDate = $employment->started_on?->year == $startDate->year ? $employment->started_on : $startDate->startOfYear();
        $endDate = $employment->ended_on?->year == $year ? $employment->ended_on : $endDate->endOfYear();

        $leavesPerYear = 30;
        $leavesPerMonth = $leavesPerYear/12;
        $leavesPerDay = $leavesPerMonth/30;
        $leaveIncrementPerYear = $this->getPreference('leave_increment_per_year', 0);
        $leaveIncrementStartsIn = $this->getPreference('leave_increment_start_year');

        // count number of years after the year, the increment starts on
        $incrementEligibleYears = $year - $leaveIncrementStartsIn;
        // +1 to include the year, increments starts from
        $incrementEligibleYears += 1;

        $yearlyLeaveIncrement = $leaveIncrementStartsIn !== null && ($incrementEligibleYears > 0)
            ? $incrementEligibleYears * $leaveIncrementPerYear : 0;

        // we calculate leaves for full months,
        // and partial months at the start and end of employment
        // ( i.e if employment doest not starts on 1st of month)

        $daysBeforeFirstMonthStarted = 0;
        if ($startDate->day !== 1){
            $daysBeforeFirstMonthStarted = $startDate->diffInDays($startDate->clone()->endOfMonth());
            $startDate->addDays($daysBeforeFirstMonthStarted + 1);
        }

        $daysAfterLastMonthEnded = 0;
        if (! $endDate->isSameDay($endDate->clone()->endOfMonth())){
            $daysAfterLastMonthEnded = $endDate->diffInDays($endDate->clone()->startOfMonth());
            $endDate->subDays($daysAfterLastMonthEnded + 1);
        }

        // full months of employments
        // we do +1, because carbon will not include the start and end date
        // and thus, gives 1 month less
        $months = $startDate->diffInMonths($endDate) + 1;

        $leaveEntitlement = $leavesPerMonth * $months;

        $leaveEntitlement += $yearlyLeaveIncrement;

        // now we calculate for any partial months
        if($daysBeforeFirstMonthStarted > 0){
            $leaveEntitlement += ($daysBeforeFirstMonthStarted * $leavesPerDay);
        }

        if ($daysAfterLastMonthEnded > 0) {
            $leaveEntitlement += ($daysAfterLastMonthEnded * $leavesPerDay);
        }

        $manuallyAddedLeaves = $this->leaveTransactions()
            ->where('transacted_on', '>=', $startDate->toDateString())
            ->where('transacted_on', '<=', $endDate->toDateString())
            ->sum('amount');

        $leaveEntitlement += $manuallyAddedLeaves;

        $leavesTaken = $this->deductibleLeaves()
            ->whereNull('rejected_at')
            ->where('ends_on', '<=', $endDate)
            ->sum('days');

        //dd($leaveEntitlement);

        return $getCurrentBalance ? $leaveEntitlement - $leavesTaken : $leaveEntitlement;
    }

    /**
     * calculate leave balance upto given date
     *
     * @param $date
     *
     * @return float
     */
    public function calculateLeaveBalance($date = null): float
    {
        // If $date is null, set it to the current date and time.
        // Otherwise, check if $date is a four-digit string, set the year of the current date to $date.
        // else, parse $date using the Carbon.

        $date = $date == null ? now() : (strlen($date) == 4 ? now()->setYear($date) : Carbon::parse($date));

        return $this->getLeaveEntitlement(endDate: $date, getCurrentBalance: true);
    }

}
