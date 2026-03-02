<?php


namespace App\Traits\Employee;


use App\Models\Vehicle;
use App\Models\VehicleDriverHistory;
use App\Models\VehicleSelection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

trait HasVehicle
{

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function vehicle()
    {
        return $this->hasOne(Vehicle::class, 'driver_id');
    }

    public function vehicleSelections()
    {
        return $this->hasMany(VehicleSelection::class);
    }

    public function driverHistories()
    {
        return $this->hasMany(VehicleDriverHistory::class, 'driver_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Methods
    |--------------------------------------------------------------------------
    */

    // every time a driver is assigned to a vehicle, a new record is created
    // in vehicle_driver_histories with handed_over_at as null.
    // when a driver hand over a vehicle, we remove the vehicle driver
    // and update handed_over_at timestamp

    public function handOverCurrentVehicle(): void
    {
        $this->vehicle?->removeDriver();

        $this->driverHistories()->whereNull('handed_over_at')->update([
            'handed_over_at' => now(),
        ]);
    }

    // check if user has selected any vehicle. this also returns true if user is not using any vehicle
    public function hasSelectedVehicle(): bool
    {
        return $this->vehicleSelections()->whereDate('created_at', now())->count();
    }

    // make a vehicle selection record for given vehicle id
    // if vehicle_id is null, we create a selection for no vehicle
    // to specify that driver is not using any vehicle for that day

    public function makeVehicleSelection(?int $vehicleId = null): void
    {
        $this->vehicleSelections()->create([
            'vehicle_id' => $vehicleId,
        ]);

        // if the driver is assigned to any other vehicle, handover it
        if ($this->vehicle && $this->vehicle->id !== $vehicleId) {
            $this->handOverCurrentVehicle();
        }

        if ($vehicleId){
            $this->assignVehicle($vehicleId);
        }

        session()->forget('vehicle_not_selected');
    }

    public function assignVehicle(int $vehicleId): void
    {
        $vehicle = Vehicle::findOrFail($vehicleId);

        // if the vehicle is already assigned to the current driver, do nothing
        if ($vehicle->driver_id == $this->id){
            return;
        }

        $vehicle->updateDriver($this->id);
    }

}
