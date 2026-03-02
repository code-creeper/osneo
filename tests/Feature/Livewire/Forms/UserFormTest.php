<?php

use App\Livewire\Forms\AttendanceForm;
use App\Livewire\Forms\UserForm;
use App\Models\User;
use Database\Seeders\PermissionSeeder;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Data\Permissions;

use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (){
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::attendanceBasic());

    $this->component = Livewire::test(UserForm::class);
});

it('can render form', function () {
    $this->component->assertSuccessful();
});

it('will validate form', function () {
    $this->component
        ->call('submit')
        ->assertHasErrors();
});

it('can submit user form', function () {
    $user = User::factory()->forPrimaryRole()->make();

    setModelValues($this->component, 'user', $user->only([
        'first_name', 'last_name', 'email', 'active', 'role_id', 'gender'
    ]));

    $this->component
        ->call('submit')
        ->assertDispatched('flashNotification', message: 'User saved');
});
