<?php

use App\Livewire\Forms\RoleForm;
use App\Models\Role;
use Database\Seeders\PermissionSeeder;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Data\Permissions;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (){
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());

    $this->component = Livewire::test(RoleForm::class);
});

it('can render form', function () {
    $this->component->assertSuccessful();
});

it('will validate form', function () {
    $this->component
        ->call('submit')
        ->assertHasErrors();
});

it('can submit form', function () {
    $this->component
        ->set('role.name', 'test')
        ->set('role.display_name', 'test')
        ->call('submit')
        ->assertDispatched('flashNotification', message: 'Role updated');

    assertDatabaseHas(Role::class, [
        'name' => 'test'
    ]);
});
