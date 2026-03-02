<?php

namespace App\Jobs;

use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateAttendanceSummariesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Carbon $startDate;
    private Carbon|string $endDate;
    private int|null $userId;

    public int $timeout = 3600;

    public function __construct(Carbon|string $startDate, Carbon|string $endDate, $userId = null)
    {
        $this->startDate = Carbon::parse($startDate);
        $this->endDate = Carbon::parse($endDate);
        $this->userId = $userId;
    }

    public function handle(): void
    {
        $users = User::query()->when(
            $this->userId,
            fn($query) => $query->where('id', $this->userId)
        )->get();

        foreach ($users as $user) {
            CarbonPeriod::create($this->startDate, $this->endDate)->forEach(
                fn($date) => $user->updateAttendanceSummary($date)
            );
        }

        Log::debug('Attendance summaries updated successfully');
    }

    public function fail($exception = null): void
    {
        Log::error($exception);
    }
}
