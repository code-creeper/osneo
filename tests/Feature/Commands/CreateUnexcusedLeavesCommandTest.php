<?php

use App\Console\Commands\CreateUnexcusedLeavesCommand;
use App\Models\Employment;
use App\Models\Leave;
use App\Models\LeaveReason;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseHas;
use function Spatie\PestPluginTestTime\testTime;

uses(RefreshDatabase::class);

beforeEach(function () {
    testTime()->freeze();
    testTime()->next('Wednesday');

    $this->user = User::factory()->create();
    $this->leaveReason = LeaveReason::factory()->create();

    config()->set('app.unexcused_leave_reason_id', $this->leaveReason->id);
});

it('can create unexcused leave', function ($date) {

    artisan(CreateUnexcusedLeavesCommand::class, [
        'date' => $date
    ]);

    // if not date is provided, it should be yesterday
    $date = $date ?? Carbon::yesterday();

    assertDatabaseHas(Leave::class, [
        'user_id' => $this->user->id,
        'starts_on' => $date->toDateString(),
        'ends_on' => $date->toDateString(),
        'reason_id' => $this->leaveReason->id,
        'approved_at' => now()->toDateTimeString()
    ]);
})->with([
    now()->previous('Monday'),
    null
]);

it('will not create leave for today or future', function () {
    Leave::forceTruncate();

    artisan(CreateUnexcusedLeavesCommand::class, ['date' => today()]);
    expect(Leave::count())->toBe(0);
});

it('will not create leave for holiday', function () {
    createHolidays(now()->subDay());

    artisan(CreateUnexcusedLeavesCommand::class);
    expect(Leave::count())->toBe(0);
});

it('will not create leave for off day', function () {
    artisan(CreateUnexcusedLeavesCommand::class, [
        'date' => now()->previous('Saturday')
    ]);

    expect(Leave::count())->toBe(0);
});

it('will not create leave if leave already exist', function () {
    Leave::forceTruncate();

    // time is set to Wednesday, so user should be on current leave
    Leave::factory()->for($this->user)->create([
        'starts_on' => now()->previous('Monday'),
        'ends_on' => now()->next('Friday'),
    ]);

    artisan(CreateUnexcusedLeavesCommand::class);

    $leavesCount = Leave::query()
        ->whereUserId($this->user->id)
        ->whereDate('starts_on', Carbon::yesterday()->toDateString())
        ->count();
    expect($leavesCount)->toBe(0);
});
