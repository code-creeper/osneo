<?php

namespace App\Livewire\Datatables;

use App\LaravelLivewireTables\Traits\WithCustomizations;
use App\Models\DocumentType;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Action;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\BooleanColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\LinkColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuItem;
use WireElements\Pro\Concerns\InteractsWithConfirmationModal;

class DocumentTypeDatatable extends DataTableComponent
{
    use InteractsWithConfirmationModal;
    use WithCustomizations;

    protected $listeners = ['refresh' => '$refresh'];

    public function boot(): void
    {
        $this->authorize('viewAny', DocumentType::class);
    }

    public function configure(): void
    {
        $this->useDefaults();
        $this->setTitle("Document Types");
    }

    public function builder(): Builder
    {
        return DocumentType::query()->withCount('properties');
    }

    public function columns(): array
    {
        return [
            Column::make("Key")->sortable(),
            Column::make("Name")->sortable(),
            BooleanColumn::make("Lexoffice")->sortable(),

            LinkColumn::make('Properties')
                ->title(fn($row) => "$row->properties_count Properties")
                ->location(fn($row) => '#')
                ->attributes(fn($row) => ['class' => 'text-info' ])
                ->wireElement(fn($row) => [
                    'type' => 'slide-over',
                    'component' => 'modals.document-properties',
                    'params' => ['documentType' => $row->id]
                ]),

            MenuColumn::make('Actions', 'actions')
                ->actions([
                    MenuItem::make('')
                        ->title(fn($row) => 'Properties')
                        ->permission(fn($row) => user()->can('update', $row))
                        ->icon('fal fa-list me-1')
                        ->wireElement(fn($row) => [
                            'type' => 'slide-over',
                            'component' => 'modals.document-properties',
                            'params' => ['documentType' => $row->id]
                        ]),

                    MenuItem::make('')
                        ->title(fn($row) => 'Edit')
                        ->icon('fal fa-pen me-1')
                        ->permission(fn($row) => user()->can('update', $row))
                        ->wireElement(fn($row) => [
                            'type' => 'modal',
                            'component' => 'forms.document-type-form',
                            'params' => ['documentType' => $row->id]
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
                ->hideIf(user()->cannot('create', DocumentType::class))
                ->wireModal('forms.document-type-form'),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Component Actions
    |--------------------------------------------------------------------------
    */

    public function delete(DocumentType $documentType): void
    {
        $this->authorize('delete', $documentType);

        $this->askForConfirmation(function () use ($documentType){
            $documentType->delete();
            $this->dispatch('flashNotification', message: __("Document Type deleted"));
        });
    }
}
