<?php

use App\Enums\ModificationType;
use App\Jobs\UpdateAttendanceSummariesJob;
use App\Models\Employment;
use App\Models\Leave;
use App\Models\LeaveDay;
use App\Models\LeaveReason;
use App\Models\Modification;
use App\Models\User;
use App\Notifications\LeaveActionTaken;
use Database\Seeders\PermissionSeeder;

use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\seed;
use function Spatie\PestPluginTestTime\testTime;

uses(RefreshDatabase::class);

describe('boot', function (){
    it('will send user a notification if leave is created by admin', function () {
        $leave = Leave::factory([
            'created_by' => User::factory()
        ])->create();

        Notification::assertSentTo($leave->user, LeaveActionTaken::class);

        $leaveCreatedBySelf = Leave::factory()->create();
        Notification::assertNothingSentTo($leaveCreatedBySelf->user);
    });

    it('will create leave days when leave is created', function (){
        $user = User::factory()->create();
        $leave = Leave::factory()->days(2, $user)->create();
        expect($leave->leaveDays()->count())->toBe(2);
    });

    it('will update leave days when leave is updated', function (){
        $leave = Leave::factory()->create();
        expect($leave->leaveDays()->count())->toBe($leave->days);

        testTime()->freeze();
        testTime()->next('Monday');

        $leave->update([
           'starts_on' => today(),
            'ends_on' => now()->next('Friday')
        ]);
        expect(LeaveDay::count())->toBe(5);
    });

    it('will delete leave days when leave is deleted', function (){
        $leave = Leave::factory()->create();
        expect(LeaveDay::count())->toBe($leave->days);

        $leave->delete();
        expect(LeaveDay::count())->toBe(0);
    });

    it('will not create leave and throw error if leave days can not be created', function (){
        expect(function () {
            $user = User::factory()->has(Employment::factory()->past())->create();
            Leave::factory()->for($user)->create();
        })
            ->toThrow(Exception::class)
            ->and(Leave::count())->toBe(0)
            ->and(LeaveDay::count())->toBe(0);
    });

    it('will not update leave and throw error if leave days can not be created', function (){
        testTime()->freeze();
        testTime()->previous('Monday');

        $starts_on = now();
        $ends_on = now()->copy()->addDays(5);

        $user = User::factory()->has(Employment::factory([
            'started_on' => now()->copy()->subMonth(),
            'ended_on' => now()->copy()->addMonth(),
        ]))->create();

        $leave = Leave::factory([
            'starts_on' => $starts_on,
            'ends_on' => $ends_on
        ])->for($user)->create();

        expect(function () use ($starts_on, $ends_on, $leave) {

            testTime()->addMonths(2);

            $leave->update([
                'starts_on' => now(),
                'ends_on' => now()->copy()->addDays(7)
            ]);
        })
            ->toThrow(Exception::class)
            ->and($leave)
            ->starts_on->toDateString()->toBe($starts_on->toDateString())
            ->ends_on->toDateString()->toBe($ends_on->toDateString())
            ->and(LeaveDay::count())->toBe(5);
    });
});

/*
|--------------------------------------------------------------------------
| Methods
|--------------------------------------------------------------------------
*/

//createModification
it('creates leave modification for delete and restore', function (ModificationType $type) {
    login();

    $leave = Leave::factory()->for(auth()->user())->create();

    $data = ['comments' => 'some comments'];

    $leave->createModification($data, $type);

    assertDatabaseHas('modifications', [
        'user_id' => auth()->id(),
        'type' => $type,
        'comments' => $data['comments'],
    ]);

})->with([
    ModificationType::Delete,
    ModificationType::Restore,
]);

//createModification
it('creates leave modification for edit', function () {
    $user = User::factory()->has(Employment::factory()->fullWeek())->create();
    login($user);

    testTime()->previous('Monday');

    $leave = Leave::factory()->for($user)->create([
        'starts_on' => now(),
        'ends_on' => now()->addDays(5),
    ]);

    $starts_on = $leave->starts_on->addDay()->toDateString();
    $ends_on = $leave->ends_on->subDay()->toDateString();
    $reason = LeaveReason::factory()->create();

    $data = [
        'starts_on' => $starts_on,
        'ends_on' => $ends_on,
        'reason_id' => $reason->id,
        'comments' => 'some comments',
    ];

    $type = ModificationType::Edit;

    $leave->createModification($data, $type);
    $modification = $leave->pendingModification;

    assertDatabaseHas('modifications', [
        'user_id' => auth()->id(),
        'type' => $type,
        'comments' => $data['comments'],
    ]);

    expect($modification->source)
        ->starts_on->toBe($leave->starts_on->toDateString())
        ->ends_on->toBe($leave->ends_on->toDateString())
        ->reason_id->toBe($leave->reason_id)
        ->and($modification->data)
        ->starts_on->toBe($starts_on)
        ->ends_on->toBe($ends_on)
        ->reason_id->toBe($reason->id);

});

//applyChanges
it('apply leave changes from modification', function () {
    testTime()->freeze();
    testTime()->previous('Monday');

    login();
    $leave = Leave::factory([
        'starts_on' => today(),
        'ends_on' => today()->addDays(3)
    ])->for(auth()->user())->create();

    $starts_on = $leave->starts_on->copy()->addDay();
    $ends_on = $leave->ends_on->copy()->addDay();
    $reason = LeaveReason::factory()->create();

    $data = [
        'starts_on' => $starts_on,
        'ends_on' => $ends_on,
        'reason_id' => $reason->id,
    ];

    $type = ModificationType::Edit;

    $leave->createModification($data, $type);

    $modification = Modification::first();

    $leave->applyChanges($modification);

    $leave->refresh();

    expect($leave)
        ->starts_on->toDateString()->toBe($starts_on->toDateString())
        ->ends_on->toDateString()->toBe($ends_on->toDateString())
        ->reason_id->toBe($reason->id);
});

//applyDeletion
it('apply leave deletion', function () {
    $leave = Leave::factory()->forUser()->create();
    $leave->applyDeletion();

    expect($leave->deleted_at)->not->toBeNull();
});

//updateAttendanceSummary
it('will dispatch UpdateAttendanceSummariesJob', function () {
    $user = User::factory()->create();

    $leave = Leave::factory([
        'starts_on' => now(),
        'ends_on' => now()->copy()->addDays(5)
    ])->for($user)->create();

    Bus::assertDispatched(UpdateAttendanceSummariesJob::class);

    //todo - might be false positive because Job might have dispatched from other places
});

//isPending
it('will check if leave is pending', function () {
    $leave = Leave::factory()->create();
    expect($leave->isPending())->toBe(true);
});

//isApproved
it('will check if leave is approved', function () {
    $leave = Leave::factory()->approved()->create();
    expect($leave->isApproved())->toBe(true);
});

//isRejected
it('will check if leave is rejected', function () {
    $leave = Leave::factory()->rejected()->create();
    expect($leave->isRejected())->toBe(true);
});

//isCreatedByAdmin
it('will check if leave is created by admin', function () {
    $leave = Leave::factory()->create();
    expect($leave->isCreatedByAdmin())->toBe(false);

    $leaveCreatedByAdmin = Leave::factory(['created_by' => User::factory()])->create();
    expect($leaveCreatedByAdmin->isCreatedByAdmin())->toBe(true);
});

//approve
it('will approve leave', function () {
    $leave = Leave::factory()->create();
    expect($leave->isApproved())->toBe(false);

    login();

    $leave->approve();
    expect($leave->isApproved())
        ->toBe(true)
        ->and($leave->approved_at->toDateTimeString())
        ->toBe(now()->toDateTimeString());
});

//reject
it('will reject leave', function () {
    testTime()->freeze();

    $leave = Leave::factory()->create();
    expect($leave->isRejected())->toBe(false);

    login();

    $leave->reject();
    expect($leave->isRejected())
        ->toBe(true)
        ->and($leave->rejected_at->toDateTimeString())
        ->toBe(now()->toDateTimeString());
});

//createLeaveDays
it('will create leave days', function () {
    testTime()->freeze();
    testTime()->previous('Friday');

    $leave = Leave::factory([
        'starts_on' => now(),
        'ends_on' => now()->next('Monday')
    ])->create();

    $leave->createLeaveDays();
    expect(LeaveDay::count())->toBe(2);
});

/*
|--------------------------------------------------------------------------
| Scopes
|--------------------------------------------------------------------------
*/

// scopeRelevant
it('can get relevant leaves', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Leave::factory()->for($user)->count(5)->create();
    Leave::factory()->for($otherUser)->count(3)->create();

    // login user without view all attendance permissions
    login($user);
    expect(Leave::relevant()->count())->toBe(5);

    seed(PermissionSeeder::class);

    // login user with view all attendance permissions
    login(user: $user, permissions: "view all leaves");
    expect(Leave::relevant()->count())->toBe(8);

});

//scopePending|scopeApproved|scopeRejected
it('can get leaves by status', function () {
    $user = User::factory()->create();

    Leave::factory()->for($user)->count(3)->create();
    Leave::factory()->for($user)->approved()->count(3)->create();
    Leave::factory()->for($user)->rejected()->count(3)->create();

    expect(Leave::status('pending')->count())->toBe(3)
        ->and(Leave::status('approved')->count())->toBe(3)
        ->and(Leave::status('rejected')->count())->toBe(3);
});

//scopeOngoing
it('can get ongoing leaves', function () {
    testTime()->freeze();

    $user = User::factory()->create();

    Leave::factory()->for($user)->create();
    Leave::factory()->for($user)->create([
        'starts_on' => now()->subDay(),
        'ends_on' => now()->addWeek()
    ]);

    expect(Leave::ongoing()->count())->toBe(1);
});

//scopeStarted
it('can get started leaves', function () {
    testTime()->freeze();

    $user = User::factory()->create();

    Leave::factory()->for($user)->create();
    Leave::factory()->for($user)->create([
        'starts_on' => now()->subDay(),
    ]);

    expect(Leave::started()->count())->toBe(1);
});

//scopeEnded
it('can get ended leaves', function () {
    testTime()->freeze();

    $user = User::factory()->create();

    Leave::factory()->for($user)->create();
    Leave::factory()->for($user)->create([
        'starts_on' => now()->subDays(7),
        'ends_on' => now()->subDays(3)
    ]);

    expect(Leave::ended()->count())->toBe(1);
});

//scopeOverLapping
it('can get overlapping leaves', function ($starts_on, $ends_on) {
    testTime()->freeze();

    $user = User::factory()->create();

    Leave::factory()->for($user)->create([
        'starts_on' => now(),
        'ends_on' => now()->addDays(7)
    ]);

    expect(Leave::overLapping($starts_on, $ends_on)->count())->toBe(1);

})->with([
    'same_dates' => [now(), now()->addDays(7)],
    'complete_overlap' => [now()->addDay(), now()->addDays(3)],
    'start_overlap' => [now()->subDays(5), now()->addDay()],
    'end_overlap' => [now()->addDays(6), now()->addDays(10)],
]);
