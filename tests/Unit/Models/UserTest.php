<?php

use App\Exceptions\EmploymentNotFoundException;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Employment;
use App\Models\Leave;
use App\Models\LeaveReason;
use App\Models\LeaveTransaction;
use App\Models\ManualEntry;
use App\Models\Modification;
use App\Models\Preference;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleDriverHistory;
use App\Models\VehicleSelection;
use Carbon\Carbon;
use Database\Seeders\LeaveReasonSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\seed;
use function Spatie\PestPluginTestTime\testTime;

uses(RefreshDatabase::class);

beforeEach(function(){
    $this->user = User::factory()->forPrimaryRole()->create();
});

test('relations', function () {
    seed(LeaveReasonSeeder::class);

    $user = User::factory()
        ->forPrimaryRole()
        ->hasAnnouncements(2)
        ->hasAttached(Announcement::factory(), [
            'read_at' => now(),
        ])

        // Trait: HasAttendance
        ->hasEmployments(3)
        ->hasAttendances()
        ->has(ManualEntry::factory()->break())
        ->has(ManualEntry::factory()->attendance())
        ->has(ManualEntry::factory()->payout())
        ->has(Modification::factory()->attendance(), 'pendingAttendances')

        // Trait: HasLeave
        ->hasLeaveTransactions()
        ->hasLeaves()
        ->has(Leave::factory()->ongoing())
        ->has(Leave::factory()->paid())
        ->has(Leave::factory()->deductible())
        ->hasLeaves(1, ['reason_id' => 1])
        ->hasLeaves(1, ['reason_id' => 2])

        // Trait: HasVehicle
        ->has(Vehicle::factory())
        ->hasVehicleSelections()
        ->hasDriverHistories()

        // Trait: HasPreferences
        ->hasPreferences()

        ->create();

    $latestEmployment = $user->employment()->latest('started_on')->first();

    expect($user->announcements)->toHaveCount(3)
        ->and($user->unreadAnnouncements)->toHaveCount(2)
        ->and($user->primaryRole->id)->toBe($user->role_id)

        // Trait: HasAttendance
        ->and($user->employments)->toHaveCount(3)
        ->and($user->employment->id)->toBe($latestEmployment->id)
        ->and($user->attendances)->toHaveCount(1)
        ->and($user->manualEntries)->toHaveCount(3)
        ->and($user->manualAttendances)->toHaveCount(1)
        ->and($user->manualBreaks)->toHaveCount(1)
        ->and($user->payouts)->toHaveCount(1)
        ->and($user->attendanceSummaries->count())->toBeGreaterThan(0) // auto generated from attendance
        ->and($user->pendingAttendances)->toHaveCount(1)

        // Trait: HasLeave
        ->and($user->leaveTransactions)->toHaveCount(1)
        ->and($user->leaves)->toHaveCount(6)
        ->and($user->futureLeaves->count())->toBeGreaterThan(1)
        ->and($user->ongoingLeaves)->toHaveCount(1)
        ->and($user->paidLeaves->count())->toBeGreaterThan(0)
        ->and($user->deductibleLeaves)->toHaveCount(1)
        ->and($user->sickLeaves)->toHaveCount(1)
        ->and($user->childSickLeaves)->toHaveCount(1)
        ->and($user->leaveDays->count())->toBeGreaterThan(0) // auto generated with leaves
        ->and($user->paidLeaveDays->count())->toBeGreaterThan(0)
        ->and($user->sickLeaveDays->count())->toBeGreaterThan(0)
        ->and($user->childSickLeaveDays->count())->toBeGreaterThan(0)

        // Trait: HasVehicle
        ->and($user->vehicle->driver_id)->toBe($user->id)
        ->and($user->vehicleSelections)->toHaveCount(1)
        ->and($user->driverHistories)->toHaveCount(1)

        // Trait: HasVehicle
        ->and($user->preferences)->toHaveCount(1);
});

/*
|--------------------------------------------------------------------------
| Trait: HasAttendance
|--------------------------------------------------------------------------
*/

//attendanceHasStarted
it('can check if attendance has started or not', function () {
    testTime()->freeze();

    $user = User::factory()->has(Attendance::factory()->active())->create();
    expect($user->attendanceHasStarted())->toBeTrue();

    testTime()->addHour();

    $user->getOrCreateActiveAttendance()->update([
        'checkout' => now()
    ]);

    expect($user->attendanceHasStarted())->toBeFalse();
});

//getOrCreateActiveAttendance
it('can get or create active attendance', function () {
    $attendance = Attendance::factory()->active()->create();
    $user = $attendance->user;

    expect($user->getOrCreateActiveAttendance()->id)->toBe($attendance->id);

    $attendance->update([
        'checkout' => now()->addHour()
    ]);

    expect($user->getOrCreateActiveAttendance()->id)->not->toBe($attendance->id);
});

//getEmployment
describe('user employment', function (){

    it('will throw exception if employment not found', function (){
        $this->user->getEmployment('2011-01-01', true);
    })->throws(EmploymentNotFoundException::class);

    it('will return new employment if no employment found', function (){
        $employment = $this->user->getEmployment('2011-01-01');

        expect($employment)
            ->id->toBeNull()
            ->weekly_target_time->toBe(0)
            ->off_days->toBe([]);
    });

    it('can get employment for given date', function () {
        $user = User::factory()->has(Employment::factory())->create();
        $employment = $user->employments()->first();

        expect($user->getEmployment())->id->toBe($employment->id);

        $otherEmployment = Employment::factory()->for($user)->create([
            'started_on' => '2021-01-01',
            'ended_on' => '2021-07-30'
        ]);

        $date = Carbon::parse('2021-03-01');
        expect($user->getEmployment($date))->id->toBe($otherEmployment->id);
    });
});

//getDailyTargetTime
it('can get daily target time', function () {
    testTime()->freeze();

    $user = User::factory()->has(Employment::factory([
        'weekly_target_time' => 40 * 60, // 2,400
        'off_days' => ['friday', 'saturday', 'sunday']
    ])->weekly())->create();

    // make sure to check for monday
    testTime()->next('monday');

    // 2,400 / 4 = 600  (15 hrs)
    expect($user->getDailyTargetTime())->toBe(600);

    // friday is off-day, so it should return 0
    testTime()->next('friday');
    expect($user->getDailyTargetTime())->toBe(0);

    //todo:: test for hourly
});

//getHourlyRate
it('can get hourly rate', function () {
    $user = User::factory()->has(Employment::factory(['hourly_rate' => 10])->active())->create();

    $otherEmployment = Employment::factory([
        'user_id' => $user->id,
        'hourly_rate' => 5
    ])->past()->create();

    expect($user)
        ->getHourlyRate()->toBe(10.0)
        ->getHourlyRate($otherEmployment->started_on)->toBe(5.0);
});

//getTotalAttendance
it('can get total attendance', function () {
    Attendance::factory(3)->for($this->user)->duration("1 hour")->create();
    expect($this->user)->getTotalAttendance()->toBe(180);
});

//getTotalBreak
it('can get total break', function () {
})->skip('method deprecated - will be removed');

//updateAttendanceSummary
it('can update attendance summary', function () {
    testTime()->freeze();

    // weekly_target_time: 600  (15 hrs)
    $user = User::factory()->has(Employment::factory()->weekly()->state([
        'weekly_target_time' => 40 * 60, // 2,400
        'off_days' => ['friday', 'saturday', 'sunday'],
        'started_on' => now()->subMonths(6),
        'ended_on' => null
    ]))->create();

    testTime()->previous('monday');

    Leave::factory()
        ->approved()
        ->paid()
        ->for($user)
        ->create([
            'starts_on' => today(),
            'ends_on' => today(),
        ]);


    Attendance::factory()->for($user)->create([
        'checkin' => now()->hour(8)->addHour()->startOfHour(),
        'checkout' => now()->hour(9)->addHour()->startOfHour()
    ]);

    ManualEntry::factory(['duration' => 60])->attendance()->for($user)->create();

    ManualEntry::factory()->attendance()->payout()->for($user)->create([
        'duration' => 60
    ]);

    $user->updateAttendanceSummary(now());

    assertDatabaseHas('attendance_summaries', [
        'user_id' => $user->id,
        'date' => now()->toDateString(),
        'target_time' => 600,
        'working_time' => 60,
        'paid_time' => 600,
        'manual_time' => 60,
        'payout_time' => 60,
        'overtime' => 120,
        'leave' => 1,
        'off_day' => 0,
        'weekend' => 0
    ]);

});

/*
|--------------------------------------------------------------------------
| Trait: HasLeave
|--------------------------------------------------------------------------
*/

//onLeave | onPaidLeave | onUnPaidLeave
it('can check if user is on leave', function () {
    testTime()->freeze();
    testTime()->next('Monday');

    $paidReason = LeaveReason::factory()->paid()->create();
    $unpaidReason = LeaveReason::factory()->unpaid()->create();

    $user = User::factory()->has(Employment::factory()->weekdays())->create();

    $leave = Leave::factory()->for($user)->create([
        'reason_id' => $paidReason->id,
        'starts_on' => now(),
        'ends_on' => now()->addDays(5)
    ]);

    expect($user->onLeave())->toBeFalse()
        ->and($user->onLeave(now()->subDay()))->toBeFalse();

    $leave->update([
        'approved_at' => now(),
        'approved_by' => User::factory()->create()
    ]);

    expect($user->onLeave())->toBeTrue()
        ->and($user->onPaidLeave())->toBeTrue();

    $leave->update([
        'reason_id' => $unpaidReason->id
    ]);

    expect($user->onUnPaidLeave())->toBeTrue()
        ->and($user->onPaidLeave())->toBeFalse();
})->skip('Error with activity log');

//createLeaveTransaction
describe('leave transaction', function (){
    it('will throw error if amount is not provided or zero', function ($amount){
        $this->user->createLeaveTransaction([
            'amount' => $amount
        ]);
    })
        ->with([null, 0])
        ->throws(Exception::class, 'Amount is required and should be non zero');

    it('can create leave transaction', function () {
        $user = User::factory()->create();
        $date = now()->addDays(3);

        $this->user->createLeaveTransaction([
            'transacted_by' => $user->id,
            'transacted_on' => $date,
            'amount' => 5,
        ]);

        assertDatabaseHas(LeaveTransaction::class, [
            'amount' => 5,
            'transacted_on' => $date->toDateString(),
            'transacted_by' => $user->id
        ]);
    });
});

//getAnnualLeaveEntitlement
it('can get annual leave entitlement', function () {

})->todo();

//getLeaveEntitlement
it('can get leave entitlement', function () {

})->todo();

//calculateLeaveBalance
it('can calculate user leave balance', function () {
    testTime()->freeze();
    testTime()->startOfYear()->next('Monday');

    $user = User::factory()->has(Employment::factory([
        'started_on' => now()->startOfYear(),
        'ended_on' => now()->endOfYear()
    ])->weekdays())->create();


    $employment = $user->employments()->first();
    expect($user->calculateLeaveBalance())->toBe(30.0);

    $user->setPreferences([
        'leave_increment_start_year' => now()->year,
        'leave_increment_per_year' => 1,
    ]);

    $employment->update([
        'started_on' => now()->subYear()->startOfYear()
    ]);

    $user->refresh();
    expect($user->calculateLeaveBalance())->toBe(61.0);

    LeaveTransaction::factory()->for($user)->create(['amount' => -10, 'transacted_on' => now()->addDay()]);

    $user->refresh();
    expect($user->calculateLeaveBalance())->toBe(51.0);

    Leave::factory([
        'starts_on' => now()->subMonth()
    ])->deductible()->days(10, $user)->for($user)->create();

    $user->refresh();

    expect($user->calculateLeaveBalance())->toBe(41.0);
});

/*
|--------------------------------------------------------------------------
| Trait: HasVehicle
|--------------------------------------------------------------------------
*/

//handOverCurrentVehicle
describe('vehicle hand over', function (){
    it('will not throw exception if driver has no vehicle to hand over', function () {
        $this->user->handOverCurrentVehicle();
        expect(1)->toBe(1);
    });

    it('can hand over currently selected vehicle', function () {
        $vehicle = Vehicle::factory()->create();

        $this->user->assignVehicle($vehicle->id);

        $vehicle->refresh();
        expect($vehicle->driver_id)->toBe($this->user->id);

        testTime()->freeze();

        $this->user->handOverCurrentVehicle();

        $vehicle->refresh();

        expect($vehicle->driver_id)->toBeNull();

        assertDatabaseHas(VehicleDriverHistory::class, [
            'vehicle_id' => $vehicle->id,
            'driver_id' => $this->user->id,
            'handed_over_at' => now()
        ]);
    });
});

//hasSelectedVehicle
it('can check if user has selected vehicle', function () {
    $this->user->makeVehicleSelection();
    expect($this->user->hasSelectedVehicle())->toBeTrue();
});

//makeVehicleSelection
describe('vehicle selection', function (){
    beforeEach(function (){
        $this->sessionKey = 'vehicle_not_selected';
        session()->put($this->sessionKey, true);
    });

    it('can make vehicle selection without vehicle id', function () {

        $this->user->makeVehicleSelection();

        assertDatabaseHas(VehicleSelection::class, [
            'user_id' => $this->user->id,
            'vehicle_id' => null
        ]);

        expect(session($this->sessionKey))->toBeNull();

    });

    it('can make vehicle selection', function () {

        $vehicle = Vehicle::factory()->create();

        $vehicle2 = Vehicle::factory()->create([
            'driver_id' => $this->user->id
        ]);

        expect($vehicle2->driver_id)->toBe($this->user->id);

        $this->user->makeVehicleSelection($vehicle->id);

        assertDatabaseHas(VehicleSelection::class, [
            'user_id' => $this->user->id,
            'vehicle_id' => $vehicle->id
        ]);

        $vehicle->refresh();
        $vehicle2->refresh();

        expect($vehicle->driver_id)->toBe($this->user->id)
            ->and($vehicle2->driver_id)->toBeNull()
            ->and(session($this->sessionKey))->toBeNull();
    });
});

/*
|--------------------------------------------------------------------------
| Trait: HasPreferences
|--------------------------------------------------------------------------
*/

//setPreferences
it('can set multiple given preferences', function () {
    $preferences = [
        'leave_increment_per_year' => 2,
        'leave_increment_start_year' => 2002
    ];

    $this->user->setPreferences($preferences);

    expect($this->user->getPreference('leave_increment_per_year'))->toBe('2')
        ->and($this->user->getPreference('leave_increment_start_year'))->toBe('2002');
});

//setPreference
describe('set preference', function () {

    it('will throw error if the given preference is not defined in constants', function () {
        $this->user->setPreference('some_random_name', 'some_random_key');
    })->throws(Exception::class, 'Preference some_random_name not found');

    it('can set given preference', function () {

        $preference = 'leave_increment_per_year';

        $this->user->setPreference($preference, 1);

        assertDatabaseHas(Preference::class, [
            'user_id' => $this->user->id,
            'role_id' => null,
            'name' => $preference,
            'value' => 1,
        ]);

    });
});

//getPreference
describe('get preference', function () {
    it('will throw error if the given preference is not defined in constants', function () {
        $this->user->getPreference('some_random_name');
    })->throws(Exception::class, 'Preference some_random_name not found');

    it('can get preference by name', function () {
        $this->user->setPreference('leave_increment_per_year', 3);
        expect($this->user->getPreference('leave_increment_per_year'))->toBe('3');
    });

    it('will return default preference value if preference is not set', function () {
        expect($this->user->getPreference('leave_increment_per_year'))->toBe(0.5)
            ->and($this->user->getPreference('leave_increment_start_year'))->toBeNull();

        $this->user->setPreference('leave_increment_per_year', 2);
        expect($this->user->getPreference('leave_increment_per_year'))->toBe('2');
    });


    //filterPreferences
    it('can filter preferences based on caller class', function () {
        $role = $this->user->primaryRole;

        $this->user->setPreference('leave_increment_per_year', 1);
        $role->setPreference('leave_increment_per_year', 3);

        expect($this->user->getPreference('leave_increment_per_year'))->toBe('1')
            ->and($role->getPreference('leave_increment_per_year'))->toBe('3');
    });
});
