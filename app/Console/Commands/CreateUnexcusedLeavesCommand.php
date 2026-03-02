<?php

namespace App\Console\Commands;

use App\Models\Leave;
use App\Models\LeaveDay;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class CreateUnexcusedLeavesCommand extends Command
{
    protected $signature = 'leave:auto-create {date?}';

    protected $description = 'Create leave for users with unexcused absence on the given date';

    public function handle(): void
    {
        $date = dateToCarbon($this->argument('date'), 'd-m-Y', Carbon::yesterday());

        if ($date->isFuture() || $date->isToday()) {
            return;
        }

        if ($date->isHoliday()) {
            return;
        }

        $usersWithoutAttendance = User::query()
            ->whereDoesntHave('attendances',
                fn(Builder $query) => $query->whereDate('date', $date)
            )
            ->with('employment', fn($employment) => $employment->forDate($date))
            ->get();

        foreach ($usersWithoutAttendance as $user) {

            if (!$user->employment){
                continue;
            }

            if ($date->isOffDay($user->employment->off_days)){
                continue;
            }

            // Check if leave already exists for the user and date
            $existingLeave = LeaveDay::where('user_id', $user->id)
                ->whereDate('date', $date)
                ->exists();

            if (!$existingLeave) {
                Leave::create([
                    'created_by' => config('app.system_user_id'),
                    'user_id' => $user->id,
                    'reason_id' => config('app.unexcused_leave_reason_id'),
                    'starts_on' => $date,
                    'ends_on' => $date,
                    'days' => 1,
                    'approved_by' => config('app.system_user_id'),
                    'approved_at' => now(),
                ]);
            }
        }

        $this->info('Leave created for users without attendance.');
    }
}
