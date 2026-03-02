<?php

use App\Enums\ModificationType;
use App\Livewire\Datatables\AttendanceDatatable;
use App\Models\Attendance;
use App\Models\Modification;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\Data\Permissions;

use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::attendanceBasic());
    $this->component = Livewire::test(AttendanceDatatable::class);
});

it('can render datatable', function () {
    $this->component->assertSuccessful();
});

it('will show attendances', function () {
    Attendance::forceTruncate();

    Attendance::factory(5)->create();

    $this->component->assertViewHas('rows', function ($rows){
        expect($rows)->toHaveCount(0);
        return true;
    });

    Attendance::factory(5)->for(user())->create();

    $this->component
        ->call('$refresh')
        ->assertViewHas('rows', function ($rows) {
            expect($rows)->toHaveCount(5);

            return true;
        });

    user()->givePermissionTo('view all attendance');

    $this->component
        ->call('$refresh')
        ->assertViewHas('rows', function ($rows) {
            expect($rows)->toHaveCount(10);

            return true;
        });
});

describe('delete attendance', function (){
    beforeEach(fn() => $this->attendance = Attendance::factory()->create([
        'user_id' => auth()->id()
    ]));

    it('can request for deletion', function (){
        $this->component
            ->call('delete', $this->attendance->id)
            ->assertDispatched('modal.open', 'modals.confirmation-modal')
            ->assertSet('confirmationCaller', 'delete')
            ->assertSet('actionConfirmed', false)
            ->dispatch('actionConfirmed', comments: 'some comments')
            ->assertDispatched('flashNotification', message: 'Request for deletion has been sent to admin');

        assertDatabaseHas(Modification::class, [
            'modifiable_type' => Attendance::class,
            'modifiable_id' => $this->attendance->id,
            'type' => ModificationType::Delete,
            'comments' => 'some comments'
        ]);
    });

    it('can delete attendance', function (){
        user()->givePermissionTo('delete attendance without approval');

        $this->component
            ->call('delete', $this->attendance->id)
            ->assertDispatched('flashNotification', message: 'Attendance deleted successfully');

        assertDatabaseHas(Attendance::class, [
            'id' => $this->attendance->id,
            'deleted_at' => now()
        ]);
    });
});

describe('restore attendance', function (){
    beforeEach(fn() => $this->attendance = Attendance::factory()->trashed()->create([
        'user_id' => auth()->id()
    ]));

    it('can request for restoration', function (){
        $this->component
            ->call('restore', $this->attendance->id)
            ->assertDispatched('flashNotification', message: 'Request for restoration has been sent to admin');

        assertDatabaseHas(Modification::class, [
            'modifiable_type' => Attendance::class,
            'modifiable_id' => $this->attendance->id,
            'type' => ModificationType::Restore
        ]);
    });

    it('can restore attendance', function (){
        user()->givePermissionTo('restore attendance without approval');

        $this->component
            ->call('restore', $this->attendance->id)
            ->assertDispatched('flashNotification', message: 'Attendance restored successfully');

        Notification::assertNothingSent();

        assertDatabaseHas(Attendance::class, [
            'id' => $this->attendance->id,
            'deleted_at' => null
        ]);
    });
});

it('can delete modification request', function () {
    user()->givePermissionTo('delete own modifications');

    $modification = Modification::factory()->attendance(ModificationType::Edit)->create();
    $attendance = $modification->modifiable;

    $this->component
        ->call('deleteModification', $attendance->id)
        ->assertDispatched('flashNotification', message: 'Modification request has been cancelled successfully');

    assertDatabaseEmpty(Modification::class);
});

describe('filters', function (){
    beforeEach(function () {
        Attendance::forceTruncate();
        loginWithPermissions(Permissions::allPermissions());

        $this->currentMonth = now()->copy()->startOfMonth();
        $this->prevMonth = now()->copy()->subMonth()->startOfMonth();
        $this->nextMonth = now()->copy()->addMonth()->startOfMonth();

        $this->attendanceCurrentMonth = 5;
        $this->attendancePrevMonth = 3;
        $this->attendanceNextMonth = 2;

        $this->user = User::factory()->create();

        Attendance::factory($this->attendancePrevMonth)->date($this->prevMonth)->create();
        Attendance::factory($this->attendanceCurrentMonth)->date($this->currentMonth)->create();
        Attendance::factory($this->attendanceNextMonth)->date($this->nextMonth)->create([
            'user_id' => $this->user->id
        ]);


        $this->component
            ->set('filterComponents.month', '')
            ->call('$refresh');
    });

    test('month filter', function (){

        $this->component
            ->set('filterComponents.month', $this->currentMonth->format('Y-m'))
            ->assertViewHas('rows', function ($rows) {
                expect($rows)->toHaveCount($this->attendanceCurrentMonth);

                return true;
            })

            ->set('filterComponents.month', $this->prevMonth->format('Y-m'))
            ->assertViewHas('rows', function ($rows) {
                expect($rows)->toHaveCount($this->attendancePrevMonth);

                return true;
            })

            ->set('filterComponents.month', $this->nextMonth->format('Y-m'))
            ->assertViewHas('rows', function ($rows) {
                expect($rows)->toHaveCount($this->attendanceNextMonth);

                return true;
            });
    });

    test('user filter', function (){
        $this->component
            ->set('filterComponents.user', $this->user->id)
            ->assertViewHas('rows', function ($rows) {
                expect($rows)->toHaveCount(2);

                return true;
            });
    });
});
