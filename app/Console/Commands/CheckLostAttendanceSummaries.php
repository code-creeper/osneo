<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\User;
use App\Models\AttendanceSummary;
use Illuminate\Console\Command;

class CheckLostAttendanceSummaries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:checklostsummaries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for missing AttendanceSummaries';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
		$users = User::all();

		$bar = $this->output->createProgressBar(count($users));
		$bar->start();
		$this->newLine();

		foreach($users as $user)
		{
			if(empty($user->deactivate_on))
			{
				$deactivate_on = Carbon::today();
			}else{
				$deactivate_on = $user->deactivate_on;
			}
			if(empty($user->activate_on))
			{
				$this->error('Error! No entry date: ' . $user->id . ' - ' . $user->first_name . ' ' . $user->last_name);
				continue;
			}


			$period = CarbonPeriod::create($user->activate_on, $deactivate_on);

			$i = 0;
			foreach ($period as $date) {
				$summary = AttendanceSummary::where('date', $date->format('Y-m-d'))->first();

				if($i == 0 AND !$summary)
				{
					$this->error('Error at User ID: ' . $user->id . ' - ' . $user->first_name . ' ' . $user->last_name);
				}

				if(!$summary)
				{
					$this->line('Summaries of ' . $date->format('Y-m-d') . ' is missing.');
					$i++;
				}
			}
			if($i != 0) $this->newLine();
			$bar->advance();
			if($i != 0) $this->newLine();
		}
		$bar->finish();
		return Command::SUCCESS;
    }
}
