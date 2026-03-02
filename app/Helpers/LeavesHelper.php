<?php


namespace App\Helpers;

use App\Models\LeaveReason;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Throwable;

class LeavesHelper
{
    public static function userCanAvailLeave(User $user, LeaveReason $reason, int $leaveRequired): bool
    {
        if ( ! $reason->deductible) {
            return true;
        }

        $leaveBalance = $user->calculateLeaveBalance();

        return $leaveBalance >= $leaveRequired;
    }

    /**
     * @throws Throwable
     */
    public static function getLeaveDates(Carbon|string $starts_on, Carbon|string $ends_on, User $user): CarbonPeriod
    {
        $starts_on = Carbon::parse($starts_on);
        $ends_on = Carbon::parse($ends_on);

        //todo:: we need to correctly check for the employment/off_days for the requested leave period.
        // e.g there can be more than 1 employment during the request leave period

        $offDays = $user->getEmployment(
            $starts_on->isFuture() ? now() : $starts_on,
            true
        )->off_days;

        return CarbonPeriod::create($starts_on, $ends_on)->addFilter('isWorkingDay', $offDays);
    }
}
