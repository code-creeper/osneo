<?php

use App\Models\Employment;
use App\Models\LeaveReason;
use App\Models\User;
use App\Models\Leave;

use App\Validation\ValidateLeaveDates;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Illuminate\Support\Facades\Validator;

use function Spatie\PestPluginTestTime\testTime;

uses(RefreshDatabase::class);

beforeEach(function () {
    testTime()->freeze();
    testTime()->startOfYear()->next('Monday');

    $this->user = User::factory()->has(Employment::factory()->weekdays())->create();
    $this->reason = LeaveReason::factory()->deductible()->create();
    $this->validator = Validator::make([], []);
});

it('will check leave dates starts and ends in same year', function () {
    login();

    $this->validator->after([
        new ValidateLeaveDates($this->user, data: [
            'reason_id' => $this->reason->id,
            'starts_on' => now()->copy()->previous('year')->lastOfYear(1),
            'ends_on' => now()->copy()->firstOfYear(1),
        ]),
    ]);

    expect($this->validator->passes())
        ->toBeFalse()
        ->and($this->validator->messages()->get('dates')[0])
        ->toBe('Vacation leaves should start and end in the same year');
});

it('will check leaves required are not zero', function () {
    $this->validator->after([
        new ValidateLeaveDates($this->user, data: [
            'reason_id' => $this->reason->id,
            'starts_on' => now()->next('Saturday'),
            'ends_on' => now()->next('Sunday')
        ]),
    ]);

    expect($this->validator->passes())
        ->toBeFalse()
        ->and($this->validator->messages()->get('dates')[0])
        ->toBe('You cannot request leave in these dates');
});

it('will check there is no other leaves on applied date', function () {
    Leave::factory()->for($this->user)->create([
        'starts_on' => now(),
        'ends_on' => now()->addDays(3)
    ]);

    $this->validator->after([
        new ValidateLeaveDates($this->user, data: [
            'reason_id' => $this->reason->id,
            'starts_on' => now(),
            'ends_on' => now()->addDays(10)
        ]),
    ]);

    expect($this->validator->passes())
        ->toBeFalse()
        ->and($this->validator->messages()->get('dates')[0])
        ->toBe('You already have a leave in these dates');

});

it('will check for leave balance', function () {
    // leave balance should be 30
    $user = User::factory()->has(
        Employment::factory([
            'started_on' => now()->startOfYear(),
            'ended_on' => now()->endOfYear()
        ])->weekdays()
    )->create();

    $this->validator->after([
        new ValidateLeaveDates($user, data: [
            'reason_id' => $this->reason->id,
            'starts_on' => now(),
            'ends_on' => now()->addMonths(4)
        ]),
    ]);

    login($user);

    expect($this->validator->passes())
        ->toBeFalse()
        ->and($this->validator->messages()->get('dates')[0])
        ->toBe('You do not have enough leaves for this category');
});
