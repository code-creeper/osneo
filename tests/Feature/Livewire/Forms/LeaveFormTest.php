<?php

use App\Enums\ModificationType;
use App\Livewire\Forms\LeaveForm;
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
use function Spatie\PestPluginTestTime\testTime;

uses(RefreshDatabase::class);

beforeEach(function (){
    testTime()->freeze();

    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::leaveBasic());

    $this->component = Livewire::test(LeaveForm::class);
});

it('can render form', function () {
    $this->component->assertSuccessful();
});

describe('create leave', function (){
    beforeEach(function (){
        $this->reason = LeaveReason::factory()->create();

        $this->startDate = now()->subDay()->toDateString();
        $this->endDate = now()->addDays(7)->toDateString();
        $this->dates = "$this->startDate to $this->endDate";
    });

    it('can request leave', function () {
        $this->component
            ->call('submit')
            ->assertHasErrors('dates')

            ->set('leave.reason_id', $this->reason->id)
            ->set('dates', $this->dates)
            ->call('submit')
            ->assertDispatched('flashNotification', message: 'Leave requested successfully');

        assertDatabaseHas(Leave::class, [
            'user_id' => user()->id,
            'starts_on' => $this->startDate,
            'ends_on' => $this->endDate,
            'approved_at' => null
        ]);
    });

    it('can create pre approve leave', function (){
        user()->givePermissionTo('create pre-approved leaves');

        $this->component
            ->call('submit')
            ->set('leave.reason_id', $this->reason->id)
            ->set('dates', $this->dates)
            ->call('submit')
            ->assertDispatched('flashNotification', message: 'Leave requested successfully');

        assertDatabaseHas(Leave::class, [
            'user_id' => user()->id,
            'starts_on' => $this->startDate,
            'ends_on' => $this->endDate,
            'approved_at' => now()
        ]);
    });

    it('can create pre approve leave for all', function (){
        user()->givePermissionTo(Permissions::leaveAdmin());
        $user = User::factory()->create();

        $this->component
            ->call('submit')
            ->set('leave.reason_id', $this->reason->id)
            ->set('leave.user_id', $user->id)
            ->set('dates', $this->dates)
            ->call('submit')
            ->assertDispatched('flashNotification', message: 'Leave requested successfully');

        assertDatabaseHas(Leave::class, [
            'user_id' => $user->id,
            'starts_on' => $this->startDate,
            'ends_on' => $this->endDate,
            'approved_at' => now()
        ]);
    });
});

describe('update leave', function (){
    beforeEach(function (){
        $this->leave = Leave::factory()->for(user())->create();
        $this->reason = LeaveReason::factory()->create();
        $this->component = Livewire::test(LeaveForm::class, ['leave' => $this->leave->id]);

    });

    it('can request for editing leave', function () {

        $this->component
            ->call('submit')
            ->assertDispatched('flashNotification', message: 'No changes were made')
            ->set('leave.reason_id', $this->reason->id)
            ->call('submit')
            ->assertDispatched('flashNotification', message: 'Request for modification has been sent to admin');

        assertDatabaseHas(Modification::class, [
            'modifiable_type' => Leave::class,
            'modifiable_id' => $this->leave->id,
            'type' => ModificationType::Edit,
        ]);
    });

    it('can edit leave', function (){
        user()->givePermissionTo('edit leaves without approval');

        $this->component
            ->set('leave.reason_id', $this->reason->id)
            ->call('submit')
            ->assertDispatched('flashNotification', message: 'Leave updated successfully');

        Notification::assertNothingSent();
        assertDatabaseHas(Leave::class, ['user_id' => user()->id, 'reason_id' => $this->reason->id]);
    });

    it('can edit leave for all', function (){
        $leave = Leave::factory()->create();

        Livewire::test(LeaveForm::class, ['leave' => $leave->id])
            ->call('submit')
            ->assertForbidden();

        user()->givePermissionTo(Permissions::leaveAdmin());

        Livewire::test(LeaveForm::class, ['leave' => $leave->id])
            ->set('leave.reason_id', $this->reason->id)
            ->call('submit')
            ->assertDispatched('flashNotification', message: 'Leave updated successfully');


        Notification::assertSentTo($leave->user, LeaveActionTaken::class);
    });
});

describe('validation', function (){
    it('will validate form', function (){
        $this->component
            ->call('submit')
            ->assertHasErrors();
    });

    test('validation rules', function ($leave, $errors){
        $user = User::factory()->create();
        $reason = LeaveReason::factory()->create();

        $setValue = fn($key, $value) => array_key_exists($key, $leave) ? $leave[$key] : $value;
        $leave = [
            'user_id' => $setValue('user_id', $user->id),
            'created_by' => $setValue('created_by', $user->id),
            'reason_id' => $setValue('reason_id', $reason->id),
            'dates' => $setValue('dates', null),
            'starts_on' => $setValue('starts_on', null),
            'ends_on' => $setValue('ends_on', null),
            'days' => $setValue('days', null),
            'selectedTags' => $setValue('selectedTags', null),
        ];

        $this->component
            ->set('leave.user_id', $leave['user_id'])
            ->set('leave.created_by', $leave['created_by'])
            ->set('leave.reason_id', $leave['reason_id'])
            ->set('dates', $leave['dates'])
            ->set('leave.starts_on', $leave['starts_on'])
            ->set('leave.ends_on', $leave['ends_on'])
            ->set('leave.days', $leave['days'])
            ->set('selectedTags', $leave['selectedTags'])
            ->call('submit')
            ->assertHasErrors($errors);
    })->with([
        'user_id is required' => [
            ['user_id' => null],
            'leave.user_id',
        ],
        'created_by should exist in database' => [
            ['created_by' => 999],
            'leave.created_by',
        ],
        'reason_id is required' => [
            ['reason_id' => null],
            'leave.reason_id',
        ],
        'reason_id should exist in database' => [
            ['reason_id' => 999],
            'leave.reason_id',
        ],
    ]);
});
