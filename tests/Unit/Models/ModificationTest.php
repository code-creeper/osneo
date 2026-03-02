<?php

use App\Enums\ModificationType;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Modification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('relations', function () {
    $leaveModification = Modification::factory()->leave(ModificationType::Edit)->create();

    expect($leaveModification->modifiable->exists())->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| Methods
|--------------------------------------------------------------------------
*/

it('can get new modifiable', function () {
    $leaveModification = Modification::factory()->leave()->create();
    $attendanceModification = Modification::factory()->attendance()->create();

    expect($leaveModification)->getNewModifiable()->toBeInstanceOf(Leave::class)
        ->and($attendanceModification)->getNewModifiable()->toBeInstanceOf(Attendance::class);
});

/*
|--------------------------------------------------------------------------
| Attributes
|--------------------------------------------------------------------------
*/

//getChangesCountAttribute
it('can get changes count', function () {
    $leave = Leave::factory()->create([
        'starts_on' => now(),
        'ends_on' => now()->addDays(5),
    ]);

    $leave->createModification([
        'starts_on' => $leave->starts_on->addDay()->toDateString(),
        'ends_on' => $leave->ends_on->subDay()->toDateString(),
    ]);

    $modification = $leave->pendingModification;

    expect($modification->changes_count)->toBe(2);

});

/*
|--------------------------------------------------------------------------
| Scopes
|--------------------------------------------------------------------------
*/

//scopePending
it('will get pending modifications', function () {
    Modification::factory(3)->create();
    Modification::factory(2)->approved()->create();

    expect(Modification::pending()->count())->toBe(3);
});

//scopeApproved
it('will get approved modifications', function () {
    Modification::factory(3)->approved()->create();
    Modification::factory(2)->create();

    expect(Modification::approved()->count())->toBe(3);
});

//scopeRelevant
it('will get relevant modifications', function () {
    login();
    $user = User::factory()->create();
    Modification::factory(2)->for($user)->create();
    Modification::factory(3)->create([
        'user_id' => auth()->id()
    ]);


    expect(Modification::relevant()->count())->toBe(3);

    loginWithPermissions('view all modifications');
    expect(Modification::relevant()->count())->toBe(5);
});
