<?php

use App\Models\Employment;
use App\Models\User;

use Illuminate\Foundation\Testing\RefreshDatabase;

use function Spatie\PestPluginTestTime\testTime;

uses(RefreshDatabase::class);

beforeEach(function (){
    $this->user = User::factory()->has(Employment::factory()->weekdays())->create();
    testTime()->freeze();
});

it('can calculate leave days', function ($startDay, $endDay, $expectedDays) {
    testTime()->next($endDay);

    $calculatedDays = LeavesHelper::getLeaveDates(
        now()->copy()->next($startDay)->toDateString(),
        now()->copy()->next($endDay)->toDateString(),
        $this->user
    )->count();

    expect($calculatedDays)->toBe($expectedDays);
})->with([
    'week_days' => ['Monday', 'Friday', 5],
    'weekends' => ['Saturday', 'Sunday', 0],
    'friday_with_weekend' => ['Friday', 'Sunday', 1],
    'weekdays_with_weekend' => ['Wednesday', 'Tuesday', 5],
]);

it('will not count holiday in calculated leave days', function () {
    testTime()->next('Monday');
    createHolidays(today());

    $calculatedDays = LeavesHelper::getLeaveDates(
        now()->toDateString(),
        now()->copy()->next('Friday')->toDateString(),
        $this->user
    )->count();

    expect($calculatedDays)->toBe(4);
});
