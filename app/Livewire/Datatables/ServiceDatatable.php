<?php

namespace App\Livewire\Datatables;

use App\LaravelLivewireTables\Traits\WithCustomizations;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Action;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuItem;
use WireElements\Pro\Concerns\InteractsWithConfirmationModal;

class ServiceDatatable extends DataTableComponent
{
    use InteractsWithConfirmationModal;
    use WithCustomizations;

    protected $listeners = ['refresh' => '$refresh'];

    public function boot(): void
    {
        $this->authorize('viewAny', Service::class);
    }

    public function configure(): void
    {
        $this->useDefaults();
        $this->setTitle("Services");
    }

    public function builder(): Builder
    {
        return Service::query()->select('services.*');
    }

    public function columns(): array
    {
        return [
            Column::make("Name")->sortable()->searchable(),
            Column::make("Category", 'category.name')->searchable(),
            Column::make("Sizes", 'sizes')->format(function ($value, $row){
                $value = "";

                foreach ($row->sizes as $size){
                    $size = (object)$size;
                    $value .= "<span class='badge badge-outline-info badge-normal mb-1 me-1'>$size->name $row->unit</span>";
                }
                return $value;
            })->html(),

            MenuColumn::make('Actions', 'actions')
                ->actions([
                    MenuItem::make('')
                        ->title(fn($row) => "Edit")
                        ->icon('fal fa-pen me-1')
                        ->permission(fn($row) => user()->can('update', $row))
                        ->wireElement(fn($row) => [
                            'type' => 'modal',
                            'component' => 'forms.service-form',
                            'params' => ['service' => $row->id]
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
                ->hideIf(user()->cannot('create', Service::class))
                ->wireModal('forms.service-form'),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Component Actions
    |--------------------------------------------------------------------------
    */

    public function delete(Service $service): void
    {
        $this->authorize('delete', $service);

        $this->askForConfirmation(function () use ($service){
            $service->delete();
            $this->dispatch('flashNotification', message: __("Service deleted"));
        });
    }
}
