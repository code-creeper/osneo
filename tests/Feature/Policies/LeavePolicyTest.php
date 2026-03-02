<?php

use App\Models\InsuranceClaim;
use App\Models\Leave;
use App\Models\Modification;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (){
    seed(PermissionSeeder::class);
});

test('viewAny attendance policy', function ($permission) {
    loginWithPermissions($permission);
    expect(auth()->user()->can('viewAny', Leave::class))->toBe($permission !== null);
})->with([
    'view all leaves',
    'view own leaves',
    null
]);

test('create leave policy', function ($permission) {
    loginWithPermissions($permission);
    expect(auth()->user()->can('create', Leave::class))->toBe($permission !== null);
})->with([
    'create leaves for all',
    'create leaves',
    null
]);

test('update leave policy', function ($permission) {
    loginWithPermissions($permission);
    $user = auth()->user();

    $leave = Leave::factory()->approved()->for($user)->create();
    expect($user->can('update', $leave))->toBeFalse();

    $leave = Leave::factory()->for($user)->active()->create();
    expect($user->can('update', $leave))->toBeFalse();

    $leave = Leave::factory()->for($user)->create();
    Modification::factory()->attendance($leave)->create();
    expect($user->can('update', $leave))->toBeFalse();

    $leave = Leave::factory()->create();
    expect($user->can('update', $leave))->toBe($permission == 'edit any attendance');

    if ($permission == 'edit own attendance') {
        $leave = Leave::factory()->for($user)->create();
        expect($user->can('update', $leave))->toBeTrue();
    }

})->with([
    'edit any leaves',
    'edit own leaves',
    null
])->todo();

test('delete leave policy', function ($permission) {
    loginWithPermissions($permission);
    $user = auth()->user();

    $leave = Leave::factory()->trashed()->create();
    expect($user->can('delete', $leave))->toBeFalse();

    $leave = Leave::factory()->create();
    Modification::factory()->for($leave, 'modifiable')->create();
    expect($user->can('delete', $leave))->toBeFalse();

    $leave = Leave::factory()->create();
    expect($user->can('delete', $leave))->toBe($permission == 'delete any leaves');

    if ($permission == 'delete own leaves') {
        $leave = Leave::factory()->for($user)->create();
        expect($user->can('delete', $leave))->toBeTrue();
    }

})->with([
    'delete any leaves',
    'delete own leaves',
    null
]);

test('approve leave policy', function () {
    loginWithPermissions('approve leaves');
    $user = auth()->user();

    $leave = Leave::factory()->approved()->create();
    expect($user->can('approve', $leave))->toBeFalse();

    $leave = Leave::factory()->rejected()->create();
    expect($user->can('approve', $leave))->toBeFalse();

    $leave = Leave::factory()->create();
    expect(User::factory()->create()->can('approve', $leave))->toBeFalse();

    $leave = Leave::factory()->create();
    expect($user->can('approve', $leave))->toBeTrue();
});

test('reject leave policy', function () {
    loginWithPermissions('reject leaves');
    $user = auth()->user();

    $leave = Leave::factory()->rejected()->create();
    expect($user->can('reject', $leave))->toBeFalse();

    $leave = Leave::factory()->rejected()->create();
    expect($user->can('reject', $leave))->toBeFalse();

    $leave = Leave::factory()->create();
    expect(User::factory()->create()->can('reject', $leave))->toBeFalse();

    $leave = Leave::factory()->create();
    expect($user->can('reject', $leave))->toBeTrue();
});

test('processClaim leave policy', function () {
    config()->set('app.sick_leave_reason_id', 10);
    loginWithPermissions('process insurance claims');
    $user = auth()->user();

    $leave = Leave::factory()->create();
    expect($user->can('processClaim', $leave))->toBeFalse();

    $leave = Leave::factory()->create();
    InsuranceClaim::factory(['leave_id' => $leave->id])->processed()->create();
    expect($user->can('processClaim', $leave))->toBeFalse();

    $leave = Leave::factory()->create();
    config()->set('app.sick_leave_reason_id', $leave->reason_id);
    expect($user->can('processClaim', $leave))->toBeTrue();
});

test('preApprove leave policy', function ($permission) {
    loginWithPermissions($permission);
    $user = auth()->user();

    $leave = Leave::factory()->create();
    expect($user->can('preApprove', $leave))->toBe($permission == 'create pre-approved leaves for all');

    if ($permission == 'create pre-approved leaves') {
        $leave = Leave::factory()->for($user)->create();
        expect($user->can('preApprove', $leave))->toBeTrue();
    }
})->with([
    'create pre-approved leaves for all',
    'create pre-approved leaves',
    null
]);
