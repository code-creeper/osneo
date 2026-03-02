<?php

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleDriverHistory;

use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseHas;
use function Spatie\PestPluginTestTime\testTime;

uses(RefreshDatabase::class);

test('relations', function () {
    $vehicle = Vehicle::factory()
        ->forDriver()
        ->hasMaintenanceHistories(2)
        ->hasDamages()
        ->hasDriverHistories()
        ->create();

    $vehicleCondition = $vehicle->maintenanceHistories()->latest()->first();

    expect($vehicle->driver->id)->toBe($vehicle->driver_id)
        ->and($vehicle->condition->id)->toBe($vehicleCondition->id)
        ->and($vehicle->maintenanceHistories)->toHaveCount(2)
        ->and($vehicle->damages)->toHaveCount(1)
        ->and($vehicle->driverHistories)->toHaveCount(1);
});


/*
|--------------------------------------------------------------------------
| Attributes
|--------------------------------------------------------------------------
*/

it('has name attribute', function () {
    $vehicle = Vehicle::factory()->create([
        'license_plate' => 'ABC',
        'manufacturer' => 'Honda',
        'model' => 'Civic',
    ]);

    expect($vehicle->name)->toBe('ABC - Honda Civic');
});

it('has last updated on attribute', function () {
    $vehicle = Vehicle::factory()->hasMaintenanceHistories()->create();
    $condition = $vehicle->condition;

    expect($vehicle->last_updated_on)->toBe($condition->created_at->toDateTimeString());
});

/*
|--------------------------------------------------------------------------
| Methods
|--------------------------------------------------------------------------
*/

//updateDriver
it('can update driver', function () {
    $vehicle = Vehicle::factory()->create();
    $user = User::factory()->create();

    testTime()->freeze();

    $vehicle->updateDriver($user->id);

    expect($vehicle->driver_id)->toBe($user->id);

    assertDatabaseHas(VehicleDriverHistory::class, [
        'vehicle_id' => $vehicle->id,
        'driver_id' => $user->id,
        'taken_at' => now(),
        'handed_over_at' => null
    ]);
});

//removeDriver
it('can remove driver', function () {
    $vehicle = Vehicle::factory()->forDriver()->create();
    expect($vehicle->driver_id)->not->toBeNull();
    $vehicle->removeDriver();
    expect($vehicle->driver_id)->toBeNull();
});
