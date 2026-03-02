<?php

namespace App\Livewire\Datatables;

use App\LaravelLivewireTables\Traits\WithCustomizations;
use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Action;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuItem;
use WireElements\Pro\Concerns\InteractsWithConfirmationModal;

class ServiceCategoryDatatable extends DataTableComponent
{
    use InteractsWithConfirmationModal;
    use WithCustomizations;

    protected $listeners = ['refresh' => '$refresh'];

    public function boot(): void
    {
        $this->authorize('viewAny', ServiceCategory::class);
    }

    public function configure(): void
    {
        $this->useDefaults();
        $this->setTitle("Service Categories");

        $this->setColumnSelectDisabled();
        $this->setPerPageVisibilityDisabled();
        $this->setSearchDisabled();
    }

    public function builder(): Builder
    {
        return ServiceCategory::query()->select('*');
    }

    public function columns(): array
    {
        return [
            Column::make("Name")->sortable()->searchable(),

            MenuColumn::make('Actions', 'actions')
                ->actions([
                    MenuItem::make('')
                        ->title(fn($row) => 'Edit')
                        ->icon('fal fa-pen me-1')
                        ->permission(fn($row) => user()->can('update', $row))
                        ->wireElement(fn($row) => [
                            'type' => 'modal',
                            'component' => 'forms.service-category-form',
                            'params' => ['serviceCategory' => $row->id]
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
                ->hideIf(user()->cannot('create', ServiceCategory::class))
                ->wireModal('forms.service-category-form'),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Component Actions
    |--------------------------------------------------------------------------
    */

    public function delete(ServiceCategory $serviceCategory): void
    {
        $this->authorize('delete', $serviceCategory);

        $this->askForConfirmation(function () use ($serviceCategory){
            $serviceCategory->delete();
            $this->dispatch('flashNotification', message: __("Service Category deleted"));
        });
    }
}
