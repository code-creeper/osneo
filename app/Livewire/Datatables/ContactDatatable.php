<?php

namespace App\Livewire\Datatables;

use App\LaravelLivewireTables\Traits\WithCustomizations;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Action;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuItem;
use WireElements\Pro\Concerns\InteractsWithConfirmationModal;

class ContactDatatable extends DataTableComponent
{
    use InteractsWithConfirmationModal;
    use WithCustomizations;

    protected $listeners = [
        'refresh' => '$refresh',
        'contactCreated' => '$refresh'
    ];

    public function boot(): void
    {
        $this->authorize('viewAny', Contact::class);
    }

    public function configure(): void
    {
        $this->useDefaults();
        $this->setTitle("Contacts");
    }

    public function builder(): Builder
    {
        return Contact::query()
            ->select('contacts.*')
            ->with('billingAddress');
    }

    public function columns(): array
    {
        return [
            Column::make("Name")->sortable()->searchable(),

            Column::make("Address")
                ->from(fn($row) => $row->billingAddress?->fullAddress())
                ->sortable(),

            Column::make("Manager", "manager.name"),
            Column::make("Email")->searchable(),
            Column::make("Phone")->searchable(),

            MenuColumn::make('Actions')
                ->actions([
                    MenuItem::make('')
                        ->title(fn($row) => 'Edit')
                        ->permission(fn($row) => user()->can('update', $row))
                        ->icon('fal fa-pen mr-1')
                        ->wireElement(fn($row) => [
                            'type' => 'modal',
                            'component' => 'forms.contact-form',
                            'params' => ['contact' => $row->id]
                        ]),

                    MenuItem::make('')
                        ->title(fn($row) => 'Delete')
                        ->icon('fa fa-trash me-1')
                        ->attributes(fn($row) => [
                            'class' => 'text-danger',
                            'wire:click' => "delete($row->id)"
                        ]),
                ]),
        ];
    }

    public function actions(): array
    {
        return [
            Action::make("Add New")
                ->primary(outline: true)
                ->icon('fal fa-plus')
                ->hideIf(user()->cannot('create', Contact::class))
                ->wireModal('forms.contact-form'),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Component Actions
    |--------------------------------------------------------------------------
    */

    public function delete(Contact $contact): void
    {
        $this->askForConfirmation(function () use ($contact){
            $this->authorize('delete', $contact);

            $this->askForConfirmation(function () use ($contact){
                $contact->delete();
                $this->dispatch('flashNotification', message: __("Contact deleted"));
            });
        });
    }
}
