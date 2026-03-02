<?php

use App\Livewire\Datatables\DamageDatatable;
use App\Models\Damage;
use App\Models\Vehicle;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Data\Permissions;

use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());

    $vehicle = Vehicle::factory()->hasDamages(3)->create();
    $this->component = Livewire::test(DamageDatatable::class, ['vehicle' => $vehicle]);
});

it('can render datatable', function () {
    $this->component->assertSuccessful();
});

it('will show damages', function () {
    $this->component
        ->call('$refresh')
        ->assertViewHas('rows', function ($rows) {
            expect($rows)->toHaveCount(3);

            return true;
        });
});


it('can delete damage', function (){
    $damage = Damage::first();

    $this->component
        ->call('delete', $damage->id)
        ->assertDispatched('modal.open', 'modal-pro-confirmation')
        ->assertSet('confirmationCaller', 'delete')
        ->assertSet('actionConfirmed', false)
        ->dispatch('actionConfirmed')
        ->assertDispatched('flashNotification', message: 'Damage deleted');

    assertDatabaseMissing(Damage::class, [
        'id' => $damage->id
    ]);
});
