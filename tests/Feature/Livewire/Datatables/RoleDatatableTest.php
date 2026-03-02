<?php

use App\Livewire\Datatables\RoleDatatable;
use App\Models\Role;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Data\Permissions;

use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());
    $this->component = Livewire::test(RoleDatatable::class);
});

it('can render datatable', function () {
    $this->component->assertSuccessful();
});

it('will show roles', function () {
    Role::factory(3)->create();

    $this->component
        ->call('$refresh')
        ->assertViewHas('rows', function ($rows) {
            expect($rows->count())->toBeGreaterThan(0);

            return true;
        });
});


it('can delete role', function (){
    $role = Role::factory()->create();

    $this->component
        ->call('delete', $role->id)
        ->assertDispatched('modal.open', 'modal-pro-confirmation')
        ->assertSet('confirmationCaller', 'delete')
        ->assertSet('actionConfirmed', false)
        ->dispatch('actionConfirmed')
        ->assertDispatched('flashNotification', message: 'Role deleted');

    assertDatabaseMissing(Role::class, [
        'id' => $role->id
    ]);
});
