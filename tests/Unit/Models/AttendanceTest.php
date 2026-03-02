<?php

use App\Enums\ModificationType;
use App\Models\Attendance;

use App\Models\Modification;
use App\Models\User;
use App\Notifications\AttendanceActionTaken;
use Carbon\Carbon;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Illuminate\Http\Request;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\seed;
use function Spatie\PestPluginTestTime\testTime;

uses(RefreshDatabase::class);

describe('boot attendance', function (){
    it('will send user a notification if attendance is created by admin', function () {
        $attendance = Attendance::factory([
            'created_by' => User::factory()
        ])->create();

        Notification::assertSentTo($attendance->user, AttendanceActionTaken::class);

        $attendanceCreatedBySelf = Attendance::factory()->create();
        Notification::assertNothingSentTo($attendanceCreatedBySelf->user);
    });

    it('will update attendance duration when created', function () {
        $attendance = Attendance::factory()->duration('3 minutes')->create();
        expect($attendance->duration)->toBe(3);

        $attendance = Attendance::factory()->active()->create();
        expect($attendance->duration)->toBeNull();
    });

    it('will update attendance duration when updated', function () {
        $attendance = Attendance::factory()->create([
            'checkin' => now(),
            'checkout' => null,
        ]);

        expect($attendance->duration)->toBeNull();

        $attendance->update([
            'checkout' => now()->addMinutes(5),
        ]);

        expect($attendance->duration)->toBe(5);
    });
});

test('relations', function () {
    $attendance = Attendance::factory()
        ->hasModifications()
        ->create();

    expect($attendance)->user->id->toBe($attendance->user_id)
        ->modifications->toHaveCount(1)
        ->pendingModification->exists()->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| Scopes
|--------------------------------------------------------------------------
*/

// scopeRelevant
it('gets relevant attendances', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Attendance::factory()->for($user)->count(5)->create();
    Attendance::factory()->for($otherUser)->count(3)->create();

    // login user without view all attendance permissions
    login($user);
    expect(Attendance::relevant()->count())->toBe(5);

    seed(PermissionSeeder::class);

    // login user with view all attendance permissions
    login(user: $user, permissions: "view all attendance");
    expect(Attendance::relevant()->count())->toBe(8);

});

// scopeActive
it('gets active attendances', function () {
    $user = User::factory()->create();

    Attendance::factory()->for($user)->past()->active()->create();
    expect(Attendance::active()->count())->toBe(0);

    Attendance::factory()->for($user)->active()->create();
    expect(Attendance::active()->count())->toBe(1);

});

//scopeCheckedOut
it('gets checked out attendances', function () {
    Attendance::factory(2)->create();
    Attendance::factory()->active()->create();

    expect(Attendance::checkedOut()->count())->toBe(2)
        ->and(Attendance::count())->toBe(3);
});

//scopeToday
it('gets attendances for today', function () {
    Attendance::factory()->date(today()->subDay())->create();
    Attendance::factory()->create();

    expect(Attendance::today()->count())->toBe(1)
        ->and(Attendance::count())->toBe(2);
});

/*
|--------------------------------------------------------------------------
| Accessors & Mutators
|--------------------------------------------------------------------------
*/

//checkin | checkout
it('can mutate checkin and checkout attribute', function ($seconds) {
    testTime()->setSecond($seconds);
    $attendance = Attendance::factory()->create();
    expect($attendance)->checkin->second->toBe(0)->checkout->second->toBe(0);
})->with([
    25,30,35
]);

//formattedDuration
it('will show attendance duration in hours and minutes format', function ($duration, $result) {
    $attendance = Attendance::factory()->duration($duration)->create();
    expect($attendance)->formatted_duration->toBe($result);
})->with([
    ["30 minutes", "00 hr 30 min"],
    ["60 minutes", "01 hr"],
    ["70 minutes", "01 hr 10 min"]
]);

/*
|--------------------------------------------------------------------------
| Methods
|--------------------------------------------------------------------------
*/

//toEventsArray
it('can map attendance collection to events format', function () {
    Attendance::forceTruncate();
    Attendance::factory(3)->create();
    $attendance = Attendance::first();

    $events = Attendance::toEventsArray(Attendance::all());
    $eventArray = [
        'id' => "a".$attendance->id,
        'resourceId' => $attendance->user_id,
        'start' => $attendance->checkin,
        'end' => $attendance->checkout,
    ];

    expect($events)->toBeArray()
        ->and($events)->toHaveCount(3)
        ->and($events[0])->toMatchArray($eventArray);

});

//hasStarted
it('can check if attendance has started', function () {
    $attendance = Attendance::factory()->active()->create();
    expect($attendance->hasStarted())->toBeTrue();

    $attendance = Attendance::factory()->create();
    expect($attendance->hasStarted())->toBeFalse();
});

//isActive
it('can check if attendance is active', function () {
    $attendance = Attendance::factory()->active()->create();
    expect($attendance->isActive())->toBe(true);

    $attendance = Attendance::factory()->date(now()->subDay())->active()->create();
    expect($attendance->isActive())->toBe(false);
});

//updateAttendanceDuration
it('can update attendance duration', function () {
    $attendance = Attendance::factory()->active()->create([
        'checkin' => now(),
    ]);
    $attendance->updateAttendanceDuration();

    expect($attendance->duration)->toBeNull();

    $attendance->updateQuietly([
        'checkout' => now()->addMinutes(5),
    ]);

    $attendance->updateAttendanceDuration();
    expect($attendance->duration)->toBe(5);
});

//createModification
describe('create attendance modification', function (){
    it('can create attendance modification for delete and restore', function (ModificationType $type) {
        login();

        $attendance = Attendance::factory()->for(auth()->user())->create();

        $data = [
            'comments' => 'some comments',
        ];

        $attendance->createModification($data, $type);

        assertDatabaseHas('modifications', [
            'user_id' => auth()->id(),
            'type' => $type,
            'comments' => $data['comments'],
        ]);

    })->with([
        ModificationType::Delete,
        ModificationType::Restore,
    ]);

    it('can create modification for edit', function () {
        login();
        $attendance = Attendance::factory()->for(auth()->user())->create();

        $checkin = $attendance->checkin->addHour();
        $checkout = $attendance->checkout->addHour();

        $data = [
            'checkin' => $checkin,
            'checkout' => $checkout,
            'comments' => 'some comments',
        ];

        $type = ModificationType::Edit;

        $attendance->createModification($data, $type);
        $modification = $attendance->pendingModification;

        assertDatabaseHas('modifications', [
            'user_id' => auth()->id(),
            'type' => $type,
            'comments' => $data['comments'],
        ]);

        expect($modification->source)
            ->checkin->toBe($attendance->checkin->toISOString())
            ->checkout->toBe($attendance->checkout->toISOString())
        ->and($modification->data)
            ->checkin->toBe($checkin->toISOString())
            ->checkout->toBe($checkout->toISOString());

    });
});

//requestCreation
it('can create modification for creation', function () {
    $data = [
        'comments' => 'some comments',
        'created_by' => User::factory()->create()->id,
        'date' => now()->toDateString(),
        'checkin' => now()->setTimeFrom('08:00'),
        'checkout' => now()->setTimeFrom('13:00'),
    ];

    Attendance::requestCreation($data);
    $modification = Modification::first();

    assertDatabaseHas('modifications', [
        'user_id' => auth()->id(),
        'type' => ModificationType::Create,
        'data' => null,
        'comments' => $data['comments'],
    ]);

    expect($modification->source)
        ->date->toBe(now()->toDateString())
        ->checkin->toBe($data['checkin']->toISOString())
        ->checkout->toBe($data['checkout']->toISOString());

});

//applyCreation
it('can create attendance from modification', function () {
    $checkin = now()->setTimeFrom('08:00');
    $checkout = now()->setTimeFrom('13:00');

    $data = [
        'comments' => 'some comments',
        'date' => now()->format('d-m-Y'),
        'checkin' => $checkin,
        'checkout' => $checkout,
    ];

    login();

    Attendance::requestCreation($data);

    $modification = Modification::first();

    $attendance = new Attendance();
    $attendance->applyCreation($modification);

    assertDatabaseHas('attendances', [
        'user_id' => auth()->id(),
        'date' => now()->toDateString(),
        'checkin' => $checkin->toDateTimeString(),
        'checkout' => $checkout->toDateTimeString(),
    ]);
});

//applyChanges
it('can apply changes from modification', function () {
    login();
    testTime()->setTime('04', '00');

    $attendance = Attendance::factory([
        'checkin' => now(),
        'checkout' => now()->copy()->addHours(2)
    ])->for(auth()->user())->create();

    $checkin = $attendance->checkin->copy()->addHour()->roundMinute();
    $checkout = $attendance->checkout->copy()->addHour()->roundMinute();

    $data = [
        'checkin' => $checkin,
        'checkout' => $checkout,
        'comments' => 'some comments',
    ];

    $type = ModificationType::Edit;

    $attendance->createModification($data, $type);

    $modification = Modification::first();

    $attendance->applyChanges($modification);
    $attendance->refresh();

    expect($attendance)
        ->checkin->toDateTimeString()->toBe($checkin->toDateTimeString())
        ->checkout->toDateTimeString()->toBe($checkout->toDateTimeString());
});

//applyDeletion
it('can apply deletion', function () {
    $attendance = Attendance::factory()->forUser()->create();
    $attendance->applyDeletion();

    expect($attendance->deleted_at)->not->toBeNull();
});

//applyRestoration
it('can apply restoration', function () {
    $attendance = Attendance::factory()->forUser()->trashed()->create();
    $attendance->applyRestoration();

    expect($attendance->deleted_at)->toBeNull();
});

//getFormattedChanges
describe('get formatted changes', function (){
    it('has formatted changes for create', function () {
        $data = [
            'date' => now()->toDateString(),
            'checkin' => now()->setTimeFrom('08:00'),
            'checkout' => now()->setTimeFrom('13:00'),
            'comments' => 'some comments',
        ];

        Attendance::requestCreation($data);
        $modification = Modification::first();

        $format = config('dates.attendance.datetime');
        $changes = [
            'checkin' => [
                'label' => 'Checkin',
                'source' => Carbon::parse($modification->source->checkin)->setDefaultTz()->format($format)
            ],
            'checkout' => [
                'label' => 'Checkout',
                'source' => Carbon::parse($modification->source->checkout)->setDefaultTz()->format($format)
            ],
        ];

        $attendance = new Attendance();
        expect($changes)->toEqual($attendance->getFormattedChanges($modification));
    });

    it('has formatted changes for edit', function () {
        $attendance = Attendance::factory()->create();

        $checkin = $attendance->checkin->copy()->addHour();
        $checkout = $attendance->checkout->copy()->addHour();

        $data = [
            'checkin' => $checkin,
            'checkout' => $checkout,
            'comments' => 'some comments',
        ];

        $attendance->createModification($data);
        $modification = Modification::first();

        $format = config('dates.attendance.datetime');
        $changes = [
            'checkin' => [
                'label' => 'Checkin',
                'source' => $attendance->checkin->format($format),
                'data' => Carbon::parse($modification->data->checkin)->setDefaultTz()->format($format)
            ],
            'checkout' => [
                'label' => 'Checkout',
                'source' => $attendance->checkout->format($format),
                'data' => Carbon::parse($modification->data->checkout)->setDefaultTz()->format($format)
            ],
        ];

        expect($changes)->toEqual($attendance->getFormattedChanges($modification));
    });

    it('has formatted changes for delete and restore', function (ModificationType $type) {
        $attendance = Attendance::factory()->forUser()->create();

        $format = config('dates.attendance.datetime');
        $attendance->createModification([], $type);

        $modification = Modification::first();

        $attendance->getFormattedChanges($modification);

        $changes = [
            'checkin' => [
                'label' => 'Checkin',
                'source' => $attendance->checkin->format($format)
            ],
            'checkout' => [
                'label' => 'Checkout',
                'source' => $attendance->checkout->format($format)
            ],
        ];

        expect($changes)->toEqual($attendance->getFormattedChanges($modification));
    })->with([
        ModificationType::Delete,
        ModificationType::Restore,
    ]);
});

//isCreatedByAdmin
it('will check if attendance is created by admin', function (Attendance $attendance, $result) {
    expect($attendance->isCreatedByAdmin())->toBe($result);
})->with([
    [fn() => Attendance::factory()->createdByUser()->create(), false],
    [fn() => Attendance::factory()->create(['created_by' => User::factory()]), true],
    [fn() => Attendance::factory()->create(), false],
]);

//isUpdatedByAdmin
it('will check if attendance is updated by admin', function (Attendance $attendance, $result) {
    expect($attendance->isUpdatedByAdmin())->toBe($result);
})->with([
    [fn() => Attendance::factory()->updatedByUser()->create(), false],
    [fn() => Attendance::factory()->create(['updated_by' => User::factory()]), true],
    [fn() => Attendance::factory()->create(), false],
]);
