<?php

use App\Livewire\Modals\SelectVehicle;
use App\Models\Vehicle;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\Data\Permissions;

use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function () {
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());

    $vehicle = Vehicle::factory()->create();
    $this->component = Livewire::test(SelectVehicle::class, ['vehicle' => $vehicle]);
});

it('can render modal', function () {
    $this->component->assertSuccessful();
});


it('can set vehicle condition', function () {

})->todo();

//onVehicleSelected
it('can handle vehicle selection', function () {
    $this->component
        ->setProperty('vehicle_id', null)
        ->call('getStepProperty')
        ->assertReturned('vehicle-selection');
})->todo();

it('can handle no vehicle selection', function () {

})->todo();
