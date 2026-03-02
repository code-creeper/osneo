<?php

namespace App\Console;

use App\Console\Commands\AddBreaks;
use App\Console\Commands\CheckoutAttendances;
use App\Console\Commands\CreateAttendanceSummary;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        AddBreaks::class,
        CheckoutAttendances::class,
		CreateAttendanceSummary::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
		// this will ensure that the task scheduler is working fine. don't need this though!
		$schedule->call(function (){
            Cache::put('last_cron_execution', now());
        })->everyMinute();

		$schedule->command('user:update-status')->dailyAt('0:01');
		$schedule->command('attendance:checkout')->dailyAt('0:05');
        //todo::improvement: remove create-summary command and use UpdateAttendanceSummariesJob
		$schedule->command('attendance:create-summary')->dailyAt('0:10');
		$schedule->command('chart:load')->dailyAt('0:30');
        $schedule->command('media-library:delete-old-temporary-uploads')->daily();
		//$schedule->command('attendance:add-breaks')->dailyAt('1:10');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
