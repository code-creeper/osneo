<?php

namespace App\Console\Commands;

use App\Models\Leave;
use App\Models\LeaveDay;
use Illuminate\Console\Command;

class CreateMissingLeaveDaysCommand extends Command
{
    protected $signature = 'leave:create-missing-days {--reset}';

    protected $description = 'Create missing leave days for leaves';

    public function handle(): void
    {
        $reset = $this->option('reset');

        if ($reset){
            LeaveDay::truncate();
        }

        $leaves = Leave::whereDoesntHave('leaveDays')->whereHas('user')->get();

        foreach ($leaves as $leave){
            try {
                $leave->createLeaveDays();
            } catch (\Error|\Exception $exception){
                $this->line($exception->getMessage());
                continue;
            }
        }

        $this->info("Done");
    }
}
