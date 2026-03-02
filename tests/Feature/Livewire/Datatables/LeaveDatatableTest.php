<?php

use App\Enums\ModificationType;
use App\Livewire\Datatables\LeaveDatatable;
use App\Models\Leave;
use App\Models\LeaveReason;
use App\Models\Modification;
use App\Models\User;
use App\Notifications\LeaveActionTaken;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\Data\Permissions;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::leaveBasic());

    $this->component = Livewire::test(LeaveDatatable::class);
});

it('can render datatable', function () {
    $this->component->assertSuccessful();
});

it('will show leaves', function () {
    Leave::forceTruncate();

    Leave::factory(5)->create();
    Leave::factory(5)->for(user())->create();

    $this->component
        ->set('filterComponents.month', '')
        ->assertViewHas('rows', function ($rows) {
            expect($rows)->toHaveCount(5);

            return true;
        });

    user()->givePermissionTo('view all leaves');

    $this->component
        ->call('$refresh')
        ->assertViewHas('rows', function ($rows) {
            expect($rows)->toHaveCount(10);

            return true;
        });
});

it('can approve leave', function () {
    auth()->user()->givePermissionTo('approve leaves');
    $leave = Leave::factory()->create();

    $this->component
        ->call('approve', $leave->id)
        ->assertSuccessful()
        ->assertDispatched('flashNotification', message: 'Leave approved successfully');

    Notification::assertSentTo($leave->user, LeaveActionTaken::class);
});

it('can reject leave', function () {
    auth()->user()->givePermissionTo('reject leaves');
    $leave = Leave::factory()->create();

    $this->component
        ->call('reject', $leave->id)
        ->assertSuccessful()
        ->assertDispatched('flashNotification', message: 'Leave rejected successfully');

    Notification::assertSentTo($leave->user, LeaveActionTaken::class);
});

describe('delete leave', function (){
    beforeEach(fn() => $this->leave = Leave::factory()->create([
        'user_id' => auth()->id()
    ]));

    it('can request for deletion', function (){
        $this->component
            ->call('delete', $this->leave->id)
            ->assertDispatched('modal.open', 'modals.confirmation-modal')
            ->assertSet('confirmationCaller', 'delete')
            ->assertSet('actionConfirmed', false)
            ->dispatch('actionConfirmed', comments: 'some comments')
            ->assertDispatched('flashNotification', message: 'Request for deletion has been sent to admin');

        assertDatabaseHas(Modification::class, [
            'modifiable_type' => Leave::class,
            'modifiable_id' => $this->leave->id,
            'type' => ModificationType::Delete,
            'comments' => 'some comments'
        ]);
    });

    it('can delete leave', function (){
        user()->givePermissionTo('delete leaves without approval');

        $this->component
            ->call('delete', $this->leave->id)
            ->assertDispatched('modal.open', 'modals.confirmation-modal')
            ->assertSet('confirmationCaller', 'delete')
            ->assertSet('actionConfirmed', false)
            ->dispatch('actionConfirmed')
            ->assertDispatched('flashNotification', message: 'Leave deleted successfully');

        assertDatabaseHas(Leave::class, [
            'id' => $this->leave->id,
            'deleted_at' => now()
        ]);
    });
});

describe('filters', function (){
    beforeEach(function () {
        Leave::forceTruncate();
        loginWithPermissions(Permissions::leaveAdmin());

        $this->component
            ->set('filterComponents.month', '')
            ->call('$refresh');

        $this->currentMonth = now()->copy()->startOfMonth();
        $this->prevMonth = now()->copy()->subMonth()->startOfMonth();
        $this->nextMonth = now()->copy()->addMonth()->startOfMonth();

        $this->leavesCurrentMonth = $this->approvedLeaves = 5;
        $this->leavesPrevMonth = $this->rejectedLeaves = 3;
        $this->leavesNextMonth = $this->pendingLeaves = 2;

        $this->reason = LeaveReason::factory()->create();
        $this->user = User::factory()->create();

        Leave::factory($this->leavesPrevMonth)->rejected()->create(['starts_on' => $this->prevMonth]);
        Leave::factory($this->leavesCurrentMonth)->approved()->create(['starts_on' => $this->currentMonth]);
        Leave::factory($this->leavesNextMonth)->create([
            'starts_on' => $this->nextMonth,
            'reason_id' => $this->reason->id,
            'user_id' => $this->user->id
        ]);
    });

    test('month filter', function (){

        $this->component
            ->set('filterComponents.month', $this->currentMonth->format('Y-m'))
            ->assertViewHas('rows', function ($rows) {
                expect($rows)->toHaveCount($this->leavesCurrentMonth);

                return true;
            })

            ->set('filterComponents.month', $this->prevMonth->format('Y-m'))
            ->assertViewHas('rows', function ($rows) {
                expect($rows)->toHaveCount($this->leavesPrevMonth);

                return true;
            })

            ->set('filterComponents.month', $this->nextMonth->format('Y-m'))
            ->assertViewHas('rows', function ($rows) {
                expect($rows)->toHaveCount($this->leavesNextMonth);

                return true;
            });
    });

    test('reason filter', function (){
        $this->component
            ->set('filterComponents.reason', $this->reason->id)
            ->assertViewHas('rows', function ($rows) {
                expect($rows)->toHaveCount(2);

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

    test('status filter', function (){
        $this->component
            ->set('filterComponents.status', 'approved')
            ->assertViewHas('rows', function ($rows) {
                expect($rows)->toHaveCount($this->approvedLeaves);

                return true;
            })

            ->set('filterComponents.status', 'rejected')
            ->assertViewHas('rows', function ($rows) {
                expect($rows)->toHaveCount($this->rejectedLeaves);

                return true;
            })

            ->set('filterComponents.status', 'pending')
            ->assertViewHas('rows', function ($rows) {
                expect($rows)->toHaveCount($this->pendingLeaves);

                return true;
            });
    });
});
