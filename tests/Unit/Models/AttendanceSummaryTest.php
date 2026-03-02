<?php

use App\Models\Address;

use App\Models\AttendanceSummary;
use Illuminate\Foundation\Testing\RefreshDatabase;


uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Methods
|--------------------------------------------------------------------------
*/

test('relations', function () {
    $summary = AttendanceSummary::factory()
        ->forUser()
        ->create();

    expect($summary->user->exists)->toBeTrue();
});

