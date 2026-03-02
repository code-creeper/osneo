<?php


namespace App\Traits\Employee;

use App\Exceptions\EmploymentNotFoundException;
use App\Models\Attendance;
use App\Models\AttendanceSummary;
use App\Models\Employment;
use App\Models\ManualEntry;
use App\Models\Modification;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait HasAttendance
{

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function employments(): HasMany
    {
        return $this->hasMany(Employment::class);
    }

    public function employment(): HasOne
    {
        return $this->hasOne(Employment::class)->latestOfMany('started_on');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function manualEntries(): HasMany
    {
        return $this->hasMany(ManualEntry::class);
    }

    public function manualAttendances(): HasMany
    {
        return $this->manualEntries()->attendance();
    }

    public function manualBreaks(): HasMany
    {
        return $this->manualEntries()->break();
    }

    public function payouts(): HasMany
    {
        return $this->manualEntries()->payout();
    }

    public function attendanceSummaries(): HasMany
    {
        return $this->hasMany(AttendanceSummary::class);
    }

    public function pendingAttendances(): HasMany
    {
        return $this->hasMany(Modification::class)
            ->where('modifiable_type', Attendance::class)
            ->pending();
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    public function attendanceHasStarted(): bool
    {
        $attendance = $this->attendances()->active()->latest()->first();

        return $attendance && $attendance->hasStarted();
    }

    public function getOrCreateActiveAttendance(): Attendance
    {
        $attendance = $this->attendances()->active()->latest()->first();

        if ($attendance && $attendance->hasStarted()) {
            return $attendance;
        }

        return $this->attendances()->create([
            'date' => today(),
        ]);
    }


    /**
     * @throws \Throwable
     */
    public function getEmployment(string|Carbon $date = null, $throwErrorIfNotFound = false)
    {
        if ($date == null) {
            $date = now();
        }

        if (is_string($date)){
            $date = Carbon::parse($date);
        }

        // todo::improvement: we can cache the employment forever and refresh only if we update the employment ?
        /*$employment_cache_key = str("employment_{$this->id}_". $date->toDateString())->snake()->value();
        $employment = Cache::tags("user_{$this->id}_employments")->remember($employment_cache_key, 60, function () use ($date) {
            return
                //($date->isToday() || $date->isFuture())
                //? $this->employment
                //:
                $this->employments()->forDate($date)->first();
        });*/

        $employment = $this->employments()->forDate($date)->first();

        if ( ! $employment) {
            throw_if(
                $throwErrorIfNotFound,
                new EmploymentNotFoundException("No employment found for User ID: $this->id on ". $date->toDateString())
            );

            $employment = new Employment([
                'weekly_target_time' => 0,
                'off_days' => [],
            ]);
        }

        return $employment;
    }

    //todo:: this seems to be incorrect! we should sum target hours from summaries
    //todo:: deprecated - will be removed
    public function getMonthlyTargetHours($year = null, $month = null): float|int
    {
        if ($month == null or $year == null) {
            $month = now()->month;
            $year = now()->year;
        }

        $date = Carbon::create($year, $month, 1);

        if ( ! $date->isWorkingDay()) {
            return 0;
        }

        $employment = $this->getEmployment($date);

        if ($date->isOffDay($employment->off_days)) {
            return 0;
        }

        $target_hours = $employment->target_hours;

        $working_days = $date->copy()->startOfMonth()
            ->diffInDaysFiltered(
                fn(Carbon $date) => $date->isWorkingDay(),
                $date->copy()->endOfMonth()
            );

        $daily_target_hours = $target_hours / (5 - count($employment->off_days));

        return $daily_target_hours * $working_days;
    }

    /**
     * Returns daily target time in minutes
     *
     * @param  Carbon|null  $date
     * @param  bool  $calculateFromMonthlyTarget  only applicable if employment type is hourly
     *
     * @return float|int
     * @throws \Throwable
     */
    public function getDailyTargetTime(Carbon $date = null, bool $calculateFromMonthlyTarget = false): float|int
    {
        if ($date == null) {
            $date = now();
        }

        $employment = $this->getEmployment($date);

        if ($date->isOffDay($employment->off_days)) {
            return 0;
        }


        $dailyTarget = $employment->weekly_target_time / (7 - count($employment->off_days));
        return $dailyTarget;

        //todo::improvement: use enum
        /*if ($employment->employment_type == 'weekly'){
            return $dailyTarget;
        }

        $totalTargetUntilToday = $this->attendanceSummaries()
            ->whereDateFormat('date', $date->format('Y-m'), 'Y-m')
            ->sum('target_time');
        $monthlyTargetLeft = $employment->monthly_target_time - $totalTargetUntilToday;
        $target = min($monthlyTargetLeft, $dailyTarget);

        return max(0, $target);*/
    }

    public function getHourlyRate(Carbon $date = null): float|int
    {
        $employment = $this->getEmployment($date);

        return $employment->hourly_rate ?? 0;
    }

    public function getTotalAttendance($date = null): float|int
    {
        $date = $date ?? Carbon::today();

        return $this->attendances()->whereDate('date', $date)->sum('duration');
    }

    //todo:: deprecated - will be removed
    public function getTotalBreak($date = null)
    {
        $date = $date ?? Carbon::today();

        // get attendances in old to new order
        $attendances = $this->attendances()
            ->with('checkin', 'checkout')
            ->whereDate('date', $date)
            ->oldest()->get();

        // get total minutes from first checkin to last checkout
        /*$total_time_duration = $attendances->first()->checkin->logged_at->diffInMinutes(
            $attendances->last()->checkout->logged_at
        );

        $total_attendance = $this->getTotalAttendance($date);

        return $total_time_duration - $total_attendance;*/


        $totalBreak = 0;

        foreach ($attendances as $index => $attendance) {
            $next = $attendances->after($attendance);

            if ( ! $next) {
                continue;
            }

            $break = $attendance->checkout->logged_at->diffInMinutes($next->checkin->logged_at);

            if ($break >= 15) {
                $totalBreak += $break;
            }
        }

        return $totalBreak;
    }

    //todo::improvement: check performance!
    public function updateAttendanceSummary(Carbon $date): void
    {
        $user_on_leave = $this->onPaidLeave($date);

        $employment = $this->getEmployment($date);

        $target_time = $this->getDailyTargetTime($date);

        if ($employment->employment_type == 'hourly') {
            $totalTargetUntilToday = $this->attendanceSummaries()
                ->whereDate('date', '<', $date->toDateString())
                ->whereDateFormat('date', $date->format('Y-m'), 'Y-m')
                ->sum('target_time');

            $monthlyTargetLeft = $employment->monthly_target_time - $totalTargetUntilToday;
            $target = min($monthlyTargetLeft, $target_time);

            $target_time = max(0, $target);
        }

        $working_time = $this->getTotalAttendance($date);
        $paid_time = ($user_on_leave || $date->isHoliday()) ? $this->getDailyTargetTime($date) : 0;

        $manualEntries = DB::table('manual_entries')
            ->selectRaw('COALESCE(SUM(CASE WHEN payout = 0 THEN duration ELSE 0 END),0) AS manual_time')
            ->selectRaw('COALESCE(SUM(CASE WHEN payout = 1 THEN duration ELSE 0 END),0) AS payout_time')
            ->where('user_id', $this->id)
            ->where('date', $date->toDateString())
            ->first();

        //todo::question: check about manual time, is that total time, attendance or break only?
        $manual_time = $manualEntries->manual_time;
        $payout_time = $manualEntries->payout_time;

        $overtime = ($working_time + $paid_time + $manual_time ) - $target_time;

        $this->attendanceSummaries()->updateOrCreate(
            ['date' => $date->toDateString()],
            [
                'target_time' => $target_time,
                'working_time' => $working_time,
                'paid_time' => $paid_time,
                'manual_time' => $manual_time,
                'payout_time' => $payout_time,
                'overtime' => $overtime,
                'leave' => (bool)$user_on_leave,
                'off_day' => $date->isOffDay($employment->off_days),
                'holiday' => $date->isHoliday(),
                'weekend' => $date->isWeekend(),
            ]
        );
    }
}
