<?php

use App\Models\Leave;
use App\Models\LeaveReason;
use App\Models\User;
use Database\Seeders\LeaveReasonSeeder;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\Data\Permissions;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertSoftDeleted;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;
use function Pest\Laravel\seed;
use function Pest\Laravel\withoutExceptionHandling;
use function Spatie\PestPluginTestTime\testTime;

uses(RefreshDatabase::class);


beforeEach(function () {
    //seed(PermissionSeeder::class);
});

describe('deprecated test', function (){
    it('can render leaves index page', function () {
        loginWithPermissions(Permissions::leaveBasic());
        get(route('leaves.index'))->assertSuccessful();
    })->todo();

    it('can render create leave page', function () {
        loginWithPermissions(Permissions::leaveBasic());
        get(route('leaves.create'))->assertSuccessful();
    })->todo();

    describe('store leave', function (){
        beforeEach(function (){
            testTime()->freeze();
            testTime()->next('Monday');

            $this->user = User::factory()->create();
            $this->reason = LeaveReason::factory()->create();

            $this->starts_on = now()->toDateString();
            $this->ends_on = now()->addDays(3)->toDateString();

            $this->leaveData = [
                'user_id' => $this->user->id,
                'reason_id' => $this->reason->id,
                'starts_on' => $this->starts_on,
                'ends_on' => $this->ends_on,
            ];
        });

        it('can create leave', function () {
            loginWithPermissions(Permissions::leaveBasic(), $this->user);

            post(
                route('leaves.store'),
                $this->leaveData
            )->assertSessionHasNoErrors();

            assertDatabaseHas('leaves', [
                'user_id' => $this->user->id,
                'starts_on' => $this->starts_on,
                'ends_on' => $this->ends_on,
                'days' => 4
            ]);

            expect(Leave::approved()->count())->toBe(0);
        });

        it('can create pre approved leave', function () {
            loginWithPermissions(Permissions::leavePreApproval(), $this->user);

            post(
                route('leaves.store'),
                $this->leaveData
            )->assertSessionHasNoErrors();

            expect(Leave::approved()->count())->toBe(1);
        });
    })->todo();

    it('can render edit leave page', function () {
        loginWithPermissions(Permissions::leaveBasic());
        $leave = Leave::factory()->for(auth()->user())->create();

        $response = get(route('leaves.edit', $leave));
        $response->assertSuccessful();
    })->todo();

    describe('update leave', function (){
        it('can update leave', function ($updatedBy) {
            testTime()->freeze();
            testTime()->next('Monday');

            $user = User::factory()->create();

            if ($updatedBy == 'self'){
                loginWithPermissions(Permissions::leaveEditWithoutApproval(), $user);
            }

            if($updatedBy == 'admin'){
                loginWithPermissions(Permissions::leaveAdmin());
            }

            $leave = Leave::factory([
                'starts_on' => today(),
                'ends_on' => today()->copy()->next('Friday')
            ])->for($user)->create();

            $reason = LeaveReason::factory()->create();
            $starts_on = today()->copy()->addDay();
            $ends_on = today()->copy()->next('Thursday');

            put(
                route('leaves.update', $leave),
                [
                    'starts_on' => $starts_on,
                    'ends_on' => $ends_on,
                    'reason_id' => $reason->id
                ]
            )->assertSessionHasNoErrors();

            assertDatabaseHas('leaves', [
                'user_id' => $user->id,
                'starts_on' => $starts_on->toDateString(),
                'ends_on' => $ends_on->toDateString(),
                'days' => 3
            ]);

            // it will send notification if leave is updated by admin
            expect($leave->user->notifications->count())->toBe($updatedBy == 'admin' ? 1 : 0);

        })->with([
            'self',
            'admin'
        ]);

        it('can not update leave without approval permission', function () {
            testTime()->freeze();
            testTime()->next('Monday');

            $user = User::factory()->create();
            loginWithPermissions(Permissions::leaveBasic(), $user);

            $leave = Leave::factory([
                'starts_on' => today(),
                'ends_on' => today()->copy()->next('Friday')
            ])->for($user)->create();

            $reason = LeaveReason::factory()->create();
            $starts_on = today()->copy()->addDay();
            $ends_on = today()->copy()->next('Thursday');

            put(
                route('leaves.update', $leave),
                [
                    'starts_on' => $starts_on,
                    'ends_on' => $ends_on,
                    'reason_id' => $reason->id
                ]
            )->assertSessionHasNoErrors();

            assertDatabaseHas('leaves', [
                'user_id' => $user->id,
                'starts_on' => today()->toDateString(),
                'ends_on' => today()->copy()->next('Friday')->toDateString(),
                'days' => 5
            ]);

            expect($leave->modifications()->pending()->count())->toBe(1);
        });
    })->todo();

    describe('delete leve', function (){
        it('can not delete leave without approval permission', function () {
            loginWithPermissions(Permissions::leaveBasic());
            $leave = Leave::factory()->for(auth()->user())->create();

            delete(route('leaves.destroy', $leave))
                ->assertRedirect()
                ->assertSessionHas('success');

            expect($leave->deleted_at)
                ->toBeNull()
                ->and($leave->modifications()->pending()->count())
                ->toBe(1);
        });

        it('can delete leave', function (){
            loginWithPermissions(Permissions::leaveDeleteWithoutApproval());
            $leave = Leave::factory()->for(auth()->user())->create();

            delete(route('leaves.destroy', $leave))
                ->assertRedirect()
                ->assertSessionHas('success');

            assertSoftDeleted('leaves', [
                'id' => $leave->id
            ]);
        });
    })->todo();

})->skip('will be removed');
