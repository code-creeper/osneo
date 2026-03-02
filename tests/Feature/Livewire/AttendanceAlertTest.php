<?php

use App\Livewire\AttendanceAlert;
use App\Livewire\AttendanceButton;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    login();
    $this->component = Livewire::test(AttendanceAlert::class);
});

it('can render attendance button', function () {
    $this->component->assertSuccessful()
        ->assertSee('Your attendance have not started.');
});

it('will will not show if attendance has started', function () {
    $attendanceComponent = Livewire::test(AttendanceButton::class);
    $attendanceComponent->call('toggleAttendance');

    $this->component
        ->call('$refresh')
        ->assertDontSee('Your attendance have not started.');
});

