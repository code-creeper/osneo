<?php

namespace App\Livewire\Forms;

use App\Helpers\ConstantHelper;
use App\Livewire\Traits\LogsActivity;
use App\Models\Damage;
use App\Models\Vehicle;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use WireElements\Pro\Components\Modal\Modal;

class DamageForm extends Modal
{
    use LogsActivity;

    use AuthorizesRequests;

    public Vehicle|int $vehicle;
    public Damage|int $damage;

    public array $rules = [
        'damage.status_id' => 'required',
        'damage.type' => 'required',
        'damage.part' => 'required',
    ];

    public function mount(Vehicle $vehicle, Damage $damage): void
    {
        $this->vehicle = $vehicle;
        $this->damage = $damage;
        $this->damage->status_id ??= ConstantHelper::getDefaultDamageStatus();
        $this->damage->vehicle_id = $vehicle->id;
    }

    public function render(): View
    {
        $this->authorize('create damages');

        $data = array();
        $data['damage_statuses'] = ConstantHelper::damageStatuses()->toKeyValuePair(value: 'value');

        return view('livewire.forms.damage-form', $data);
    }

    public function submit(): void
    {
        $this->validate();

        $this->damage->user_id = auth()->id();
        $this->damage->vehicle_id = $this->damage->vehicle_id ?? $this->vehicle->id;

        if ( ! $this->damage->id && ! user()->can('update damage status')) {
            $this->damage->status_id = ConstantHelper::getDefaultDamageStatus();
        } elseif ( ! user()->can('update damage status')) {
            $this->damage->status_id = $this->damage->getOriginal('status_id');
        }

        $this->damage->save();

        $this->close(andDispatch: [
            'refresh',
            'flashNotification' => ['message' => __('Damage reported')],
        ]);
    }

    public static function attributes(): array
    {
        return [
            'size' => '2xl'
        ];
    }
}
