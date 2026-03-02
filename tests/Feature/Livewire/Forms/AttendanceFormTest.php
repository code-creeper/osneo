<?php

use App\Livewire\Forms\AttendanceForm;
use App\Models\Attendance;
use App\Models\Modification;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\Data\Permissions;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (){
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::attendanceBasic());

    $this->component = Livewire::test(AttendanceForm::class);
});

it('can render attendance form', function () {
    $this->component->assertSuccessful();
});

it('will not show hidden fields', function () {
    $this->component->assertPropertyWired('date')

        ->set('hiddenFields', ['date', 'user_id'])
        ->assertPropertyNotWired('date')
        ->assertPropertyNotWired('user_id');
});

it('will show notice and comments input when editing and user needs approval', function () {
    $this->component
        ->assertDontSee('alert alert-info')
        ->assertPropertyNotWired('comments')

        ->set('editing', true)
        ->assertSee('alert alert-info')
        ->assertPropertyWired('comments');
});

it('will not show notice and comments input when editing and user does not needs approval', function () {
    user()->givePermissionTo('edit attendance without approval');
    $this->component
        ->set('editing', true)
        ->assertDontSee('alert alert-info')
        ->assertPropertyNotWired('comments');
});

it('will not show user and date input when editing', function () {
    $this->component
        ->set('editing', true)
        ->assertPropertyNotWired('attendance.user_id')
        ->assertPropertyNotWired('date');
});

describe('create attendance form', function (){
    beforeEach(fn() => $this->component
        ->set('date', now()->subDay()->date())
        ->set('checkin', '08:00')
        ->set('checkout', '09:00')
    );

    it('will check if user can create attendance for all', function (){
        $user = User::factory()->create();

        $this->component
            ->set('attendance.user_id', $user->id)
            ->call('submit')
            ->assertForbidden();
    });

    it('can create attendance for all', function (){
        $user = User::factory()->create();
        user()->givePermissionTo('create manual attendance for all');
        $this->component
            ->set('attendance.user_id', $user->id)
            ->call('submit')
            ->assertSuccessful();

        assertDatabaseHas(Attendance::class, [
            'user_id' => $user->id,
        ]);
    });

    it('can request for creating attendance', function (){
        $this->component
            ->call('submit')
            ->assertSuccessful()
            ->assertDispatched('flashNotification', message: 'Request for creating attendance has been sent to admin');

        assertDatabaseHas(Modification::class, [
            'modifiable_type' => Attendance::class,
            'modifiable_id' => 0
        ]);
    });

    it('can submit new attendance form', function () {
        user()->givePermissionTo(Permissions::attendanceWithoutApproval());

        $this->component
            ->call('submit')
            ->assertSuccessful()
            ->assertDispatched('flashNotification', message: 'Attendance created');

        assertDatabaseHas(Attendance::class, [
            'user_id' => auth()->id(),
        ]);
    });
});

describe('edit attendance form', function (){
    beforeEach(function () {
        // important: we make sure the attendance is not in the future, so make attendance for yesterday
        $this->attendance = Attendance::factory()->date(now()->subDay())->create([
            'checkin' => '06:00',
            'checkout' => '11:00',
        ]);

        $this->component = Livewire::test(AttendanceForm::class, ['attendance' => $this->attendance->id]);
    });

    it('will check if user can edit attendance for all', function (){
        $this->component
            ->call('submit')
            ->assertForbidden();
    });

    it('can edit attendance for all', function (){
        user()->givePermissionTo('edit any attendance');
        $this->component
            ->call('submit')
            ->assertSuccessful();

        expect($this->attendance->user_id)->not->toBe(user()->id);

        assertDatabaseHas(Attendance::class, [
            'user_id' => $this->attendance->user_id,
        ]);
    });

    it('can request for editing attendance', function (){
        $this->component
            ->set('attendance.user_id', user()->id)
            ->set('checkin', '07:00')
            ->call('submit')
            ->assertSet('editing', true)
            ->assertSuccessful()
            ->assertHasNoErrors()
            ->assertDispatched('flashNotification', message: 'Request for modification has been sent to admin');

        assertDatabaseHas(Modification::class, [
            'modifiable_type' => Attendance::class,
            'modifiable_id' => $this->attendance->id
        ]);
    });

    it('can submit self attendance form', function () {
        user()->givePermissionTo(Permissions::attendanceWithoutApproval());

        $this->component
            ->set('attendance.user_id', auth()->id())
            ->call('submit')
            ->assertSuccessful()
            ->assertDispatched('flashNotification', message: 'Attendance updated');

        assertDatabaseHas(Attendance::class, [
            'user_id' => auth()->id(),
        ]);
    });
});

describe('form validation', function (){

});
