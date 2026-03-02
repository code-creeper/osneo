<?php

namespace Database\Seeders;

use App\Models\User;
use Artisan;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();

        $start_time = [9,9,9,9,9,10,9,9,9,9,9,9];
        $end_time = [17,17,17,18,17,17,18,18,17,17,19,17,19,17,20,20,17,19,20];

        foreach ($users as $user) {
            $period = CarbonPeriod::create('2021-09-01', '2021-11-11');

            foreach ($period as $date) {
                if ($date->isWeekend()){
                    continue;
                }

                $attendance = $user->attendances()->create([
                    'created_at' => $date,
                    'updated_at' => $date
                ]);

                // checkin
                $time = $start_time[array_rand($start_time)];
                $checked_in_at = Carbon::createFromFormat('Y-m-d H:i:s', "{$date->format('Y-m-d')} $time:00:00");

                $attendance->checkin()->create([
                    'type' => 'checkin',
                    'logged_at' => $checked_in_at,
                    'created_at' => $checked_in_at,
                    'updated_at' => $checked_in_at,
                ]);

                // checkout
                $time = $end_time[array_rand($end_time)];
                $checked_out_at = Carbon::createFromFormat('Y-m-d H:i:s', "{$date->format('Y-m-d')} $time:00:00");

                $attendance->checkin()->create([
                    'type' => 'checkout',
                    'logged_at' => $checked_out_at,
                    'created_at' => $checked_out_at,
                    'updated_at' => $checked_out_at,
                ]);

                $user->updateAttendanceSummary($date);
            }
        }
    }
}
