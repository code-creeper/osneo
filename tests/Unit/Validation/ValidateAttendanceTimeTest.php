<?php

use App\Models\Attendance;
use App\Models\User;
use App\Validation\ValidateAttendanceTime;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Spatie\PestPluginTestTime\testTime;

uses(RefreshDatabase::class);

beforeEach(function () {
    testTime()->freeze();

    $this->user = User::factory()->create();
    $this->validator = Validator::make([], []);
    $this->attribute = 'attendance';

    // attendance: 8 - 10

    $testTimes = [
        ['checkin' => '08:00', 'checkout' => '10:00'], // same
        ['checkin' => '07:00', 'checkout' => '09:00'], // start overlap
        ['checkin' => '09:00', 'checkout' => '11:00'], // end overlap
        ['checkin' => '08:30', 'checkout' => '09:30'], // subset
        ['checkin' => '07:00', 'checkout' => '11:00'], // superset

        ['checkin' => '06:00', 'checkout' => '08:00'], // ends at checkin
        ['checkin' => '10:00', 'checkout' => '11:00'], // starts at checkout
    ];
});


it('will check checkin and checkout are valid', function ($checkoutHour) {
    testTime()->hour(9);

    $this->validator->after([
        new ValidateAttendanceTime(now()->hour(8), now()->hour($checkoutHour))
    ]);

    expect($this->validator->passes())
        ->toBe(false)
        ->and($this->validator->messages()->get($this->attribute)[0])
        ->toBe('Invalid checkout time');
})->with([
    'checkout_before_checkin' => 6,
    'future_checkout' => 10,
    'checkin_same_checkout' => 8,
]);

it('will check attendance does not overlap with existing attendance', function ($checkin, $checkout) {
    testTime()->hour(15);

    Attendance::factory([
        'checkin' => now()->hour(8),
        'checkout' => now()->hour(10)
    ])->for($this->user)->create();

    $this->validator->after([
        new ValidateAttendanceTime(
            now()->setTimeFrom($checkin),
            now()->setTimeFrom($checkout),
            $this->user
        ),
    ]);

    expect($this->validator->passes())
        ->toBe(false)
        ->and($this->validator->messages()->get($this->attribute)[0])
        ->toBe('You already have an attendance in this time');

})->with([
    'same'          => ['checkin' => '08:00', 'checkout' => '10:00'], // same
    'start_overlap' => ['checkin' => '07:00', 'checkout' => '09:00'], // start overlap
    'end_overlap'   => ['checkin' => '09:00', 'checkout' => '11:00'], // end overlap
    'subset'        => ['checkin' => '08:30', 'checkout' => '09:30'], // subset
    'superset'      => ['checkin' => '07:00', 'checkout' => '11:00'], // superset
]);

it('will check attendance does not overlap with pending attendance', function ($checkin, $checkout) {
    testTime()->hour(15);

    Attendance::requestCreation([
        'date' => today()->toDateString(),
        'checkin' => now()->hour(8),
        'checkout' => now()->hour(10),
        'user_id' => $this->user->id,
    ]);

    $this->validator->after([
        new ValidateAttendanceTime(
            now()->setTimeFrom($checkin),
            now()->setTimeFrom($checkout),
            $this->user
        ),
    ]);

    expect($this->validator->passes())
        ->toBe(false)
        ->and($this->validator->messages()->get($this->attribute)[0])
        ->toBe('You already have a pending attendance in this time');

})->with([
    'same'          => ['checkin' => '08:00', 'checkout' => '10:00'], // same
    'start_overlap' => ['checkin' => '07:00', 'checkout' => '09:00'], // start overlap
    'end_overlap'   => ['checkin' => '09:00', 'checkout' => '11:00'], // end overlap
    'subset'        => ['checkin' => '08:30', 'checkout' => '09:30'], // subset
    'superset'      => ['checkin' => '07:00', 'checkout' => '11:00'], // superset
]);
