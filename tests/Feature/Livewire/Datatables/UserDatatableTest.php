<?php

use App\Livewire\Datatables\UserDatatable;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Data\Permissions;

use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());
    $this->component = Livewire::test(UserDatatable::class);
});

it('can render datatable', function () {
    $this->component->assertSuccessful();
});

it('will show users', function () {
    User::factory(3)->create();

    $this->component
        ->call('$refresh')
        ->assertViewHas('rows', function ($rows) {
            expect($rows->count())->toBeGreaterThan(0);

            return true;
        });
});

it('can delete user', function (){
    $user = User::factory()->create();

    $this->component
        ->call('delete', $user->id)
        ->assertDispatched('modal.open', 'modal-pro-confirmation')
        ->assertSet('confirmationCaller', 'delete')
        ->assertSet('actionConfirmed', false)
        ->dispatch('actionConfirmed')
        ->assertDispatched('flashNotification', message: 'User deleted');

    assertDatabaseMissing(User::class, [
        'id' => $user->id
    ]);
});
