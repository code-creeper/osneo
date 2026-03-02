<?php

use App\Console\Commands\CreateAttendanceSummary;
use App\Models\AttendanceSummary;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Symfony\Component\Console\Command\Command;

use function Pest\Laravel\artisan;
use function Spatie\PestPluginTestTime\testTime;

uses(RefreshDatabase::class);

it('will not create attendance summary for future date', function () {
    testTime()->freeze();
    AttendanceSummary::truncate();

    artisan(CreateAttendanceSummary::class, [
        'date' => now()->addDay()
    ])->assertExitCode(Command::SUCCESS);

    expect(AttendanceSummary::count())->toBe(0);
});

it('will only create attendance summary for given user and date', function () {
    User::factory()->count(5)->create();
    $user = User::factory()->create();
    $date = now()->subDays(10);

    artisan(CreateAttendanceSummary::class, [
        'date' => $date,
        'user_id' => $user->id,
    ])->assertExitCode(Command::SUCCESS);

    $summary = AttendanceSummary::first();

    expect(AttendanceSummary::count())->toBe(1)
        ->and($summary)->user_id->toBe($user->id)
        ->date->format('Y-m-d')->toBe($date->format('Y-m-d'));
});

it('will create attendance summary for all users for yesterday if user and date is not given', function () {
    AttendanceSummary::truncate();
    User::forceTruncate();

    User::factory()->count(5)->create();

    artisan(CreateAttendanceSummary::class)->assertExitCode(Command::SUCCESS);

    expect(AttendanceSummary::whereDate('date', Carbon::yesterday())->count())->toBe(5);
});
