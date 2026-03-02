<?php

namespace App\Livewire\Datatables;

use App\LaravelLivewireTables\Traits\WithCustomizations;
use App\Models\Damage;
use App\Models\Vehicle;
use App\Models\VehicleDriverHistory;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Action;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuItem;
use WireElements\Pro\Concerns\InteractsWithConfirmationModal;

class VehicleDatatable extends DataTableComponent
{
    use InteractsWithConfirmationModal;
    use WithCustomizations;

    protected $listeners = ['refresh' => '$refresh'];

    public function boot(): void
    {
        $this->authorize('viewAny', Vehicle::class);
    }

    public function configure(): void
    {
        $this->useDefaults();
        $this->emptyHeader();

        $this->setTitle("Vehicles");
    }

    public function builder(): Builder
    {
        return Vehicle::query()->select('vehicles.*');
    }

    public function columns(): array
    {
        return [
            Column::make("Name")->from(fn($row) => $row->name),
            Column::make("License Plate")->sortable(),
            Column::make("Ticket Number")->sortable(),
            Column::make("Last Updated On")->from(fn($row) => $row->last_updated_on),
            Column::make("Driver")->from(fn($row) => $row->driver->name),

            MenuColumn::make('Actions', 'actions')
                ->actions([
                    MenuItem::make('')
                        ->title(fn($row) => 'Update Condition')
                        ->permission(fn($row) => user()->can('create', VehicleDriverHistory::class))
                        ->icon('fal fa-file-edit me-1')
                        ->wireElement(fn($row) => [
                            'type' => 'modal',
                            'component' => 'modals.select-vehicle',
                            'params' => [
                                'vehicle' => $row->id,
                                'updateDriver' => false
                            ]
                        ]),

                    MenuItem::make('')
                        ->title(fn($row) => 'Report Damage')
                        ->permission(fn($row) => user()->can('create', Damage::class))
                        ->icon('fal fa-car-crash me-1')
                        ->wireElement(fn($row) => [
                            'type' => 'modal',
                            'component' => 'forms.damage-form',
                            'params' => ['vehicle' => $row->id]
                        ]),

                    MenuItem::make('')
                        ->title(fn($row) => 'Details')
                        ->permission(fn($row) => user()->can('update', $row))
                        ->location(fn($row) => route('vehicles.show', $row))
                        ->icon('fal fa-list-alt me-1'),

                    MenuItem::make('')
                        ->title(fn($row) => 'Edit')
                        ->icon('fal fa-pen me-1')
                        ->permission(fn($row) => user()->can('update', $row))
                        ->wireElement(fn($row) => [
                            'type' => 'modal',
                            'component' => 'forms.vehicle-form',
                            'params' => ['vehicle' => $row->id]
                        ]),

                    MenuItem::make('')
                        ->title(fn($row) => 'Delete')
                        ->icon('fa fa-trash me-1')
                        ->permission(fn($row) => user()->can('delete', $row))
                        ->attributes(fn($row) => [
                            'class' => 'text-danger',
                            'wire:click' => "delete($row->id)"
                        ])
                ]),
        ];
    }

    public function actions(): array
    {
        return [
            Action::make("Add New")
                ->primary(outline: true)
                ->icon('fal fa-plus')
                ->hideIf(user()->cannot('create', Vehicle::class))
                ->wireModal('forms.vehicle-form'),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Component Actions
    |--------------------------------------------------------------------------
    */

    public function delete(Vehicle $vehicle): void
    {
        $this->authorize('delete', $vehicle);

        $this->askForConfirmation(function () use ($vehicle){
            $vehicle->delete();
            $this->dispatch('flashNotification', message: __("Vehicle deleted"));
        });
    }
}
