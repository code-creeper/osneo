<?php

use App\Livewire\Forms\VehicleForm;
use App\Models\Vehicle;
use App\Models\VehicleMaintenanceHistory;
use Database\Seeders\PermissionSeeder;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Data\Permissions;

use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (){
    seed(PermissionSeeder::class);
    loginWithPermissions(Permissions::allPermissions());

    $this->component = Livewire::test(VehicleForm::class);
});

it('can render form', function () {
    $this->component->assertSuccessful();
});

it('will validate form', function () {
    $this->component
        ->call('submit')
        ->assertHasErrors();
});

describe('submit form', function (){
    beforeEach(fn() => fillVehicleForm($this->component));

    it('can submit form', function () {
        $this->component
            ->call('submit')
            ->assertDispatched('flashNotification', message: 'Vehicle saved');
    });

    it('will open vehicle selection modal if vehicle does not have any condition', function () {
        $this->component
            ->call('submit')
            ->assertDispatched('modal.open', component: 'modals.select-vehicle')
            ->assertDispatched('flashNotification', message: 'Vehicle saved');
    });

    it('will not open vehicle selection modal if vehicle have any condition', function () {
        $vehicle = Vehicle::factory()->hasMaintenanceHistories()->create();
        $component = Livewire::test(VehicleForm::class, ['vehicle' => $vehicle->id]);
        fillVehicleForm($component, $vehicle);

        $component
            ->call('submit')
            ->assertNotDispatched('modal.open', component: 'modals.select-vehicle')
            ->assertDispatched('flashNotification', message: 'Vehicle saved');
    });
});


function fillVehicleForm($component, $vehicle = null): void
{
    $vehicle = $vehicle ?? Vehicle::factory()->make();

    setModelValues($component, 'vehicle', $vehicle->only([
        'license_plate', 'ticket_number', 'manufacturer', 'model'
    ]));
}
