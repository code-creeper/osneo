<?php

namespace App\Livewire\Forms;

use App\Livewire\Traits\LogsActivity;
use App\Models\Vehicle;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use WireElements\Pro\Components\Modal\Modal;

class VehicleForm extends Modal
{
    use LogsActivity;

    public Vehicle|int $vehicle;

    public string $heading;
    public bool $editing = false;

    protected function rules(): array
    {
        return [
            'vehicle.license_plate' => 'required',
            'vehicle.ticket_number' => 'required',
            'vehicle.manufacturer' => 'required',
            'vehicle.model' => 'required',
        ];
    }

    public function mount(Vehicle $vehicle): void
    {
        $this->heading = __('Add New Vehicle');

        $this->vehicle = $vehicle;

        if ($this->vehicle->id){
            $this->editing = true;
            $this->heading = __('Edit Vehicle');
        }
    }

    public function render(): View
    {
        $data = array();

        return view('livewire.forms.vehicle-form', $data);
    }

    public function submit(): void
    {
        $this->validate();

        $this->vehicle->save();

        $this->dispatch('flashNotification', message: 'Vehicle saved');

        if ( ! $this->vehicle->condition->exists) {
            $this->dispatch(
                'modal.open',
                component: 'modals.select-vehicle',
                arguments: [
                    'vehicle' => $this->vehicle->id,
                    'updateDriver' => false,
                ]
            );

            return;
        }

        $this->close(andDispatch: 'refresh');
    }

    public static function attributes(): array
    {
        return [
            'size' => '2xl'
        ];
    }
}
