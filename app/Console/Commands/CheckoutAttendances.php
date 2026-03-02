<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Notifications\AttendanceUpdated;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckoutAttendances extends Command
{
    protected $signature = 'attendance:checkout';

    protected $description = 'Checkout users';

    public function handle(): void
    {
        $date = Carbon::yesterday();
        $attendances = Attendance::whereDate('date', $date)->whereNull('checkout')->get();

        $bar = $this->output->createProgressBar(count($attendances));
        $bar->start();

        foreach ($attendances as $attendance) {
            $attendance->update([
                'updated_by' => config('app.system_user_id'),
                'checkout' => $attendance->checkin->endOfDay()->floorMinute(),
                'comments' => 'Checkout was forgotten and set by the system',
            ]);

            $attendance->user->notify(new AttendanceUpdated($attendance));
            Log::debug('Attendance ID: '.$attendance->id.' checked out by System');

            $bar->advance();
        }

        $bar->finish();
    }

}
