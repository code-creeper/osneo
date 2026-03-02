<?php

use App\Livewire\AttendanceButton;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Spatie\PestPluginTestTime\testTime;

uses(RefreshDatabase::class);

beforeEach(function (){
    $this->component = Livewire::test(AttendanceButton::class);
});

it('can render component', function () {
    $this->component->assertSuccessful();
});

it('can toggle attendance', function () {
    Attendance::forceTruncate();
    login();
    $user = auth()->user();

    $this->component->call('toggleAttendance');
    expect($user->attendances()->active()->count())->toBe(1);

    testTime()->addHour();

    $this->component->call('toggleAttendance');
    expect($user->attendances()->count())->toBe(1);
});
