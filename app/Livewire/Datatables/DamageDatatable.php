<?php

namespace App\Livewire\Datatables;

use App\LaravelLivewireTables\Traits\WithCustomizations;
use App\Models\Damage;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Action;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuItem;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;
use WireElements\Pro\Concerns\InteractsWithConfirmationModal;

class DamageDatatable extends DataTableComponent
{
    use InteractsWithConfirmationModal;
    use WithCustomizations;

    protected $listeners = ['refresh' => '$refresh'];

    public Vehicle $vehicle;

    public function boot(): void
    {
        $this->authorize('viewAny', Damage::class);
    }

    public function mount(Vehicle $vehicle): void
    {
        $this->vehicle = $vehicle;
    }

    public function configure(): void
    {
        $this->useDefaults();
        $this->emptyHeader();
    }

    public function builder(): Builder
    {
        return Damage::query()->select('damages.*')
            ->where('vehicle_id', $this->vehicle->id);
    }

    public function columns(): array
    {
        return [
            Column::make("Type"),
            Column::make("Part"),
            Column::make("Reported By")->from(fn($row) => $row->user->name),
            Column::make("Status")->from(fn($row) => $row->status),

            MenuColumn::make('Actions', 'actions')
                ->actions([
                    MenuItem::make('')
                        ->title(fn($row) => 'Edit')
                        ->icon('fal fa-pen me-1')
                        ->permission(fn($row) => user()->can('update', $row))
                        ->wireElement(fn($row) => [
                            'type' => 'modal',
                            'component' => 'forms.damage-form',
                            'params' => ['damage' => $row->id]
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

    public function filters(): array
    {
        return [
            SelectFilter::make('Vehicle')
                ->options(Vehicle::all()->toKeyValuePair())
                ->setFirstOption()
                ->hiddenFromAll()
                ->filter(fn(Builder $builder, string $value) => $builder->where('vehicle_id', $value)),
        ];
    }

    public function actions(): array
    {
        return [
            Action::make("Add New")
                ->primary(outline: true)
                ->icon('fal fa-plus')
                ->hideIf(
                    ! $this->vehicle->id
                    || user()->cannot('create', Damage::class)
                )
                ->wireModal('forms.damage-form', ['vehicle' => $this->vehicle->id]),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Component Actions
    |--------------------------------------------------------------------------
    */

    public function delete(Damage $damage): void
    {
        $this->authorize('delete', $damage);

        $this->askForConfirmation(function () use ($damage){
            $damage->delete();
            $this->dispatch('flashNotification', message: __("Damage deleted"));
        });
    }
}
