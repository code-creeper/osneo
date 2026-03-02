<?php

use App\Console\Commands\CheckoutAttendances;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Symfony\Component\Console\Command\Command;

use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseHas;
use function Spatie\PestPluginTestTime\testTime;

uses(RefreshDatabase::class);

it('can checkout user attendances', function () {
    Attendance::forceTruncate();

    Attendance::factory()
        ->date(Carbon::yesterday())
        ->active()
        ->count(3)
        ->create();

    artisan(CheckoutAttendances::class)->assertExitCode(Command::SUCCESS);

    expect(Attendance::whereNull('checkout')->count())->toBe(0);

    Attendance::each(function ($attendance){
        assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'comments' => 'Checkout was forgotten and set by the system',
            'updated_by' => (int)config('app.system_user_id'),
            'checkout' => Carbon::yesterday()->endOfDay()->floorMinute()->toDateTimeString(),
        ]);
    });
});

it('can only checkout yesterday attendance', function () {

    Attendance::factory()
        ->active()
        ->date(today())
        ->count(2)
        ->create();

    Attendance::factory()
        ->active()
        ->date(today()->subDays(2))
        ->count(2)
        ->create();

    artisan(CheckoutAttendances::class)->assertExitCode(Command::SUCCESS);
    expect(Attendance::whereNull('checkout')->count())->toBe(4);
});
