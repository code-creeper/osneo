<?php

namespace App\Livewire\Datatables;

use App\LaravelLivewireTables\Traits\WithCustomizations;
use App\Models\DocumentProperty;
use App\Models\DocumentType;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Action;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuItem;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;
use WireElements\Pro\Concerns\InteractsWithConfirmationModal;

class DocumentPropertyDatatable extends DataTableComponent
{
    use InteractsWithConfirmationModal;
    use WithCustomizations;

    protected $listeners = [
        'refresh' => '$refresh',
        'setFilter' => 'setFilterEvent',
    ];

    public DocumentType $documentType;

    public function boot(): void
    {
        $this->authorize('viewAny', DocumentProperty::class);
    }

    public function mount(DocumentType $documentType): void
    {
        $this->documentType = $documentType;
    }

    public function configure(): void
    {
        $this->useDefaults();
        $this->setTitle("Document Properties");

        $this->setSearchDisabled();
        $this->setColumnSelectDisabled();
        $this->setPaginationDisabled();
    }

    public function builder(): Builder
    {
        return DocumentProperty::query()
            ->where('document_type_id', $this->documentType->id)
            ->select('document_properties.*');
    }

    public function columns(): array
    {
        return [
            Column::make("Key")->sortable(),
            Column::make("Name")->sortable(),
            Column::make("Type")->sortable(),
            Column::make("Rules")->sortable(),
            Column::make("Order")->sortable(),
            Column::make("Is Name")->sortable(),
            Column::make("Active")->sortable(),

            MenuColumn::make('Actions', 'actions')
                ->actions([
                    MenuItem::make('')
                        ->title(fn($row) => 'Edit')
                        ->icon('fal fa-pen me-1')
                        ->permission(fn($row) => user()->can('update', $row))
                        ->wireElement(fn($row) => [
                            'type' => 'modal',
                            'component' => 'forms.document-property-form',
                            'params' => ['documentProperty' => $row->id]
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
                ->hideIf(
                    ! $this->documentType->id
                    || user()->cannot('create', DocumentProperty::class)
                )
                ->wireModal('forms.document-property-form', [
                    'documentType' => $this->documentType->id
                ]),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Component Actions
    |--------------------------------------------------------------------------
    */

    public function delete(DocumentProperty $documentProperty): void
    {
        $this->authorize('delete', $documentProperty);

        $this->askForConfirmation(function () use ($documentProperty){
            $documentProperty->delete();
            $this->dispatch('flashNotification', message: __("Document Property deleted"));
        });
    }
}
