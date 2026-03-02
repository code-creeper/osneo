<?php

use App\Models\LeaveReason;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('relations', function () {
    $leaveReason = LeaveReason::factory()->hasLeaves(2)->create();

    expect($leaveReason->leaves->count())->toBe(2);
});



