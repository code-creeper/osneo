<?php

use App\Models\Attendance;
use App\Models\Modification;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\seed;

/*uses(RefreshDatabase::class);

beforeEach(function (){
    seed(PermissionSeeder::class);
});

test('viewAny attendance policy', function ($permission) {
    $result = $permission !== null;
    loginWithPermissions($permission);
    expect(auth()->user()->can('viewAny', Attendance::class))->toBe($result);
})->with([
    'view all attendance',
    'view own attendance',
    null
]);

test('create attendance policy', function ($permission) {
    $result = $permission !== null;
    loginWithPermissions($permission);
    expect(auth()->user()->can('create', Attendance::class))->toBe($result);
})->with([
    'create manual attendance for all',
    'create manual attendance',
    'create attendance',
    null
]);

test('update attendance policy', function ($permission) {
    loginWithPermissions($permission);

    $user = auth()->user();
    $attendance = Attendance::factory()->for($user)->trashed()->create();
    expect($user->can('update', $attendance))->toBeFalse();

    $attendance = Attendance::factory()->for($user)->active()->create();
    expect($user->can('update', $attendance))->toBeFalse();

    $attendance = Attendance::factory()->for($user)->create();
    Modification::factory()->attendance($attendance)->create();
    expect($user->can('update', $attendance))->toBeFalse();

    $attendance = Attendance::factory()->create();
    expect($user->can('update', $attendance))->toBe($permission == 'edit any attendance');

    if ($permission == 'edit own attendance') {
        $attendance = Attendance::factory()->for($user)->create();
        expect($user->can('update', $attendance))->toBeTrue();
    }

})->with([
    'edit any attendance',
    'edit own attendance',
    null
]);

test('delete attendance policy', function ($permission) {
    loginWithPermissions($permission);
    $user = auth()->user();

    $attendance = Attendance::factory()->for($user)->trashed()->create();
    expect($user->can('delete', $attendance))->toBeFalse();

    $attendance = Attendance::factory()->for($user)->active()->create();
    expect($user->can('delete', $attendance))->toBeFalse();

    $attendance = Attendance::factory()->for($user)->create();
    Modification::factory()->attendance($attendance)->create();
    expect($user->can('delete', $attendance))->toBeFalse();

    $attendance = Attendance::factory()->create();
    expect($user->can('delete', $attendance))->toBe($permission == 'delete any attendance');

    if ($permission == 'delete own attendance') {
        $attendance = Attendance::factory()->for($user)->create();
        expect($user->can('delete', $attendance))->toBeTrue();
    }

})->with([
    'delete any attendance',
    'delete own attendance',
    null
]);

test('restore attendance policy', function ($permission) {
    loginWithPermissions($permission);
    $user = auth()->user();

    $attendance = Attendance::factory()->for($user)->create();
    expect($user->can('restore', $attendance))->toBeFalse();

    $attendance = Attendance::factory()->for($user)->trashed()->create();
    Modification::factory()->attendance($attendance)->create();
    expect($user->can('restore', $attendance))->toBeFalse();

    $attendance = Attendance::factory()->trashed()->create();
    expect($user->can('restore', $attendance))->toBe($permission == 'restore any attendance');

    if ($permission == 'restore own attendance') {
        $attendance = Attendance::factory()->trashed()->for($user)->create();
        expect($user->can('restore', $attendance))->toBeTrue();
    }

})->with([
    'restore any attendance',
    'restore own attendance',
    null
]);

test('deleteModification attendance policy', function ($permission) {
    loginWithPermissions($permission);
    $user = auth()->user();

    $attendance = Attendance::factory()->for($user)->create();
    expect($user->can('deleteModification', $attendance))->toBeFalse();

    $attendance = Attendance::factory()->for($user)->create();
    Modification::factory()->attendance($attendance)->create();
    expect($user->can('deleteModification', $attendance))->toBe($permission == 'delete own modifications');

    $attendance = Attendance::factory()->create();
    Modification::factory(['user_id' => User::factory()])->attendance($attendance)->create();
    expect($user->can('deleteModification', $attendance))->toBeFalse();

})->with([
    'delete own modifications',
    null
]);

test('storeManually attendance policy', function ($permission) {
    loginWithPermissions($permission);
    expect(auth()->user()->can('storeManually', Attendance::class))->toBe($permission !== null);
})->with([
    'create manual attendance for all',
    'create manual attendance',
    null
]);*/

