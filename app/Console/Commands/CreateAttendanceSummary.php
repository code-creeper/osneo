<?php

namespace App\Console\Commands;

use App\Helpers\GeneralHelper;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class CreateAttendanceSummary extends Command
{
    protected $signature = 'attendance:create-summary {date?} {user_id?}';

    protected $description = 'Create daily summary of attendance';

    public function handle(): void
    {
        $date = $this->argument('date');
        $user_id = $this->argument('user_id');

        $date = dateToCarbon($date, 'd-m-Y');

        if (!$date){
            $date = now()->subDay();
        }

        if ($date->isFuture()) {
            return;
        }

        $users = User::query()
            ->when($user_id,
                fn(Builder $query) => $query->where('id', $user_id)
            )
            ->get();

        $bar = $this->output->createProgressBar(count($users));
        $bar->start();

        foreach ($users as $user) {
            $user->updateAttendanceSummary($date);
            $bar->advance();
        }

        $bar->finish();
    }
}
