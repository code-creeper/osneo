<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\ManualEntry;
use App\Models\User;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class AddBreaks extends Command
{
    protected $signature = 'attendance:add-breaks {date?}';

    protected $description = 'Add manual breaks to the entries where needed';

    public function handle(): void
    {
        $date    = $this->argument('date');

        if ($date && ! $date instanceof \Carbon\Carbon){
            $date = Carbon::createFromFormat('d-m-Y', $date);
        } else {
            $date = now()->subDay();
        }

        $users = User::whereHas('attendances',
            fn(Builder $query) => $query->whereDate('date', $date)
        )->with('attendances')->get();

        foreach ($users as $user){
            $breakNeeded = 0;

            //TODO::improvement: values should not be hard coded
            $breakUnderNineHrs = 30;
            $breakOverNineHrs = 45;

            $hours = $user->getTotalAttendance($date) / 60;
            $breakTaken = $user->getTotalBreak($date);

            // if total hours are greater than 6 and less than 9
            if ($hours >= 6 && $hours < 9 && $breakTaken < $breakUnderNineHrs){
                $breakNeeded = $breakUnderNineHrs - $breakTaken;
            }

            if ($hours >= 9 && $breakTaken < $breakOverNineHrs){
                $breakNeeded = $breakOverNineHrs - $breakTaken;
            }

            if ($breakNeeded){
                ManualEntry::create([
                    'type' => 'break',
                    // if break needed is less than 15 mins, make it 15 mins
                    'duration' => $breakNeeded < 15 ? 15 : $breakNeeded,
                    'user_id' => $user->id,
                    'date' => $date->format('Y-m-d'),
                ]);
            }
        }

        print "Done!";
    }
}
