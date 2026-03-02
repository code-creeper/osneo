<?php

namespace App\Livewire\Modals;

use App\Livewire\Traits\LogsActivity;
use App\Models\Vehicle;
use App\Models\VehicleMaintenanceHistory;
use Illuminate\Contracts\View\View;
use WireElements\Pro\Components\Modal\Modal;

class SelectVehicle extends Modal
{
    use LogsActivity;

    public VehicleMaintenanceHistory $history;

    public ?int $vehicle_id;
    public bool $showMaintenanceForm = false;
    public bool $confirmationRequired = false;
    public bool $vehicleChangeable = true;
    public bool $updateDriver = true;
    public bool $damagesReviewed = false;

    //dates
    public string $firstAidKitExpiryDate;
    public string $craftsmanLicenseExpiryDate;
    public string $nextMaintenanceDate;
    public string $motDate;

    public array $rules = [
        'history.vehicle_id' => 'required',
        'history.mileage' => 'required',
        'history.outside_condition' => 'required',
        'history.inside_condition' => 'required',
        'history.tank_level' => 'required',
        'history.gas_card' => 'required',
        'history.safety_vest' => 'required',
        'history.first_aid_kit' => 'required',
        'firstAidKitExpiryDate' => 'required_if:history.first_aid_kit,yes',
        'history.craftsman_license' => 'required',
        'craftsmanLicenseExpiryDate' => 'required_if:history.craftsman_license,yes',
        'history.registration' => 'required',
        'history.service_booklet' => 'required',
        'history.back_left_tyre_profile' => 'required',
        'history.front_right_tyre_profile' => 'required',
        'history.back_right_tyre_profile' => 'required',
        'history.front_left_tyre_profile' => 'required',
        'nextMaintenanceDate' => 'required',
        'motDate' => 'required',
        'history.warning_triangle' => 'required',
        'history.emission_sticker' => 'required',
        'damagesReviewed' => 'accepted'
    ];

    public Vehicle|int $vehicle;

    public function mount(?Vehicle $vehicle = null, bool $updateDriver = true): void
    {
        if ($vehicle->exists) {
            $this->vehicle = $vehicle;
            $this->vehicle_id = $vehicle->id;
            $this->showMaintenanceForm = true;
            $this->vehicleChangeable = false;
        } else {
            $this->vehicle_id = auth()->user()->vehicle
                ? auth()->user()->vehicle->id
                : null;
        }

        $this->updateDriver = $updateDriver;

        $this->setVehicleCondition();
    }

    public function placeholderConfig(): string
    {
        return 'line:classes=w-75|line^5:ct=mt-4,cb=mb-4|button:classes=mb-0';
    }

    public function render(): View
    {
        $data = array();
        $data['vehicles'] = Vehicle::all();
        $data['title'] = !$this->vehicle_id ? __('Select Vehicle') : __('Update vehicle condition');

        return view('livewire.modals.select-vehicle', $data);
    }

    public function setVehicleCondition(): void
    {
        $currentCondition = null;

        if ($this->vehicle_id) {
            $currentCondition = VehicleMaintenanceHistory::where([
                'vehicle_id' => $this->vehicle_id,
            ])->latest('id')->first();
        }

        $this->history = $currentCondition ? $currentCondition->replicate() : new VehicleMaintenanceHistory();

        $this->history->vehicle_id = $this->history->vehicle_id ?? $this->vehicle_id;

        if ($currentCondition){
            $this->firstAidKitExpiryDate = $currentCondition->first_aid_kit_expiry?->date();
            $this->craftsmanLicenseExpiryDate = $currentCondition->craftsman_license_expiry?->date();
            $this->nextMaintenanceDate = $currentCondition->next_maintenance_date?->date();
            $this->motDate = $currentCondition->mot_date?->date();
        }
    }

    public function onVehicleSelected(): void
    {
        $this->vehicle = Vehicle::findOrFail($this->vehicle_id);

        if ($this->vehicle->driver_id && $this->vehicle->driver_id !== auth()->id()) {
            $this->confirmationRequired = true;
        }

        if ( ! $this->maintenanceFormIsRequired()) {
            auth()->user()->makeVehicleSelection($this->vehicle->id);
            $this->saveSelection();

            return;
        }

        $currentCondition = $this->vehicle->maintenanceHistories()->latest('id')->first();

        $this->history = $currentCondition
            ? $currentCondition->replicate([
                'user_id' => auth()->id(),
            ])
            : new VehicleMaintenanceHistory([
                'user_id' => auth()->id(),
                'vehicle_id' => $this->vehicle->id,
            ]);

        $this->showMaintenanceForm = true;
    }

    public function noVehicleSelected(): void
    {
        auth()->user()->makeVehicleSelection();
        $this->saveSelection();
    }

    public function submit(): void
    {
        $this->validate($this->rules);

        $this->history->user_id = auth()->id();

        $this->history->first_aid_kit_expiry = $this->firstAidKitExpiryDate;
        $this->history->craftsman_license_expiry = $this->craftsmanLicenseExpiryDate;
        $this->history->next_maintenance_date = $this->nextMaintenanceDate;
        $this->history->mot_date = $this->motDate;

        $this->history->save();

        if ($this->updateDriver){
            auth()->user()->makeVehicleSelection($this->vehicle_id);
        }

        $this->saveSelection();
    }

    public function saveSelection(): void
    {
        $this->close(andDispatch: [
            'flashNotification' => ['message' => __('Selection Saved')]
        ]);
    }

    public function maintenanceFormIsRequired(): bool
    {
        $vehicle = $this->vehicle;

        if ($vehicle->driver_id !== auth()->id()) {
            return true;
        }

        $vehicleLastUpdatedOn = $vehicle->last_updated_on;
        $lastFridayOfMonth = now()->lastFridayOfMonth();

        if ($lastFridayOfMonth->isFuture()) {
            $lastFridayOfMonth = now()->subMonth()->startOfMonth()->lastFridayOfMonth();
        }

        $vehicleUpdatedOnLastFriday = $vehicleLastUpdatedOn->gte($lastFridayOfMonth);

        if ( ! $vehicleUpdatedOnLastFriday) {
            return true;
        }

        // False, if driver have same vehicle
        return $vehicle->driver_id !== auth()->id();
    }

    public static function attributes(): array
    {
        return [
            'size' => '5xl'
        ];
    }
}
