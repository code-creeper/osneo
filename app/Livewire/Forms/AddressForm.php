<?php

namespace App\Livewire\Forms;

use App\Enums\HeatingSystem;
use App\Livewire\Traits\LogsActivity;
use App\Models\Address;
use Illuminate\Contracts\View\View;
use WireElements\Pro\Components\Modal\Modal;

class AddressForm extends Modal
{
    use LogsActivity;

    public Address|int $address;

    public string $heading;
    public bool $editing = false;

    protected function rules(): array
    {
        return [
            'address.street' => 'required',
            'address.zip_code' => 'required',
            'address.city' => 'required',
            'address.is_service_location' => 'boolean',
            'address.heating_system' => 'required_if:address.is_service_location,true',
        ];
    }

    public function mount(Address $address): void
    {
        $this->heading = __('Create Address');
        $this->address = $address;

        if ($this->address->id) {
            $this->editing = true;
            $this->heading = __('Edit Address');
        } else {
            $this->address->is_service_location = 0;
        }
    }

    public function render(): View
    {
        $data = array();

        $data['heatingSystems'] = HeatingSystem::toArray();

        return view('livewire.forms.address-form', $data);
    }

    public function submit(): void
    {
        $this->validate();

        $this->address->save();

        $this->close(andDispatch: [
            'addressCreated' => [$this->address->id],
            'flashNotification' => ['message' => __('Address created')],
        ]);
    }

    public static function attributes(): array
    {
        return [
            'size' => '7xl',
        ];
    }
}
