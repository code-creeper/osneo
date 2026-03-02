<?php

use App\Models\Announcement;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;


uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Relations
|--------------------------------------------------------------------------
*/

test('relations', function () {
    $announcement = Announcement::factory()
        ->hasUsers(3)
        ->hasAttached(User::factory(2), [
            'read_at' => now()
        ])
        ->create();


    expect($announcement->users)->toHaveCount(5)
    ->and($announcement->readRecipients)->toHaveCount(2);
});

