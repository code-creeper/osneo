<?php

use App\Livewire\Datatables\VehicleDatatable;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Data\Permissions;

use function Pest\Laravel\assertDatabaseEmpty;

uses(RefreshDatabase::class);

beforeEach(function () {
    seedPermissions();
    loginWithPermissions(Permissions::allPermissions());
    $this->component = Livewire::test(VehicleDatatable::class);
});

it('can render datatable', function () {
    $this->component->assertSuccessful();
});

it('will show vehicles', function () {
    Vehicle::factory(3)->create();

    $this->component
        ->call('$refresh')
        ->assertViewHas('rows', function ($rows) {
            expect($rows)->toHaveCount(3);

            return true;
        });
});


it('can delete vehicle', function (){
    $vehicle = Vehicle::factory()->create();

    $this->component
        ->call('delete', $vehicle->id)
        ->assertDispatched('modal.open', 'modal-pro-confirmation')
        ->assertSet('confirmationCaller', 'delete')
        ->assertSet('actionConfirmed', false)
        ->dispatch('actionConfirmed')
        ->assertDispatched('flashNotification', message: 'Vehicle deleted');

    assertDatabaseEmpty(Vehicle::class);
});
