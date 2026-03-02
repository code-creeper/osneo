<?php

namespace App\Console\Commands;

use App\Models\Employment;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class CorrectPayoutCommand extends Command
{
    protected $signature = 'payout:correct {userId?}';

    protected $description = 'Correct the payouts after migrating from v1.2 to v1.3';

    // Todo:: remove the command after correction
    public function handle(): void
    {
        $userId = $this->argument('userId');

        $users = User::with('employments')
            ->when($userId, fn(Builder $query) => $query->where('id', $userId))
            ->get();

        foreach ($users as $user){
            foreach ($user->employments as $employment){
                if ( $employment->employment_type !== 'hourly') {
                    continue;
                }

                $endDate = $employment->ended_on ?? Carbon::parse('2023-09-01');

                $period = CarbonPeriod::create($employment->started_on, '1 month', $endDate);

                foreach ($period as $date){
                    $this->addPayout($date, $user, $employment);
                }
            }
        }

        $this->info("Finished");
    }


    private function addPayout(Carbon $date, User $user, Employment $employment): void
    {
        $working_days = $date->copy()->startOfMonth()
            ->diffInDaysFiltered(
                fn(Carbon $date) => !$date->isOffDay($employment->off_days),
                $date->copy()->endOfMonth()
            );

        $weekdays = 7 - count($employment->off_days);
        $dailyTargetTime = $employment->weekly_target_time / $weekdays;
        $daysToPayout = $working_days - (4 * $weekdays);

        $payoutTime = 0 - $daysToPayout * $dailyTargetTime;
        $payoutHours = $payoutTime/60;

        $this->info("$user->name: Added $payoutHours hours for ". $date->format('F Y'));

        if ( ! $payoutTime) {
            return;
        }

        $user->manualEntries()->updateOrCreate([
            'logged_by' => config('app.system_user_id'),
            'date' => $date->endOfMonth()->toDateString(),
            'payout' => 1,
            'comments' => 'Payout correction for v1.3',
        ], [
            'duration' => $payoutTime,
        ]);
    }
}
