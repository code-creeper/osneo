<?php

namespace App\Livewire\Datatables;

use App\LaravelLivewireTables\Traits\WithCustomizations;
use App\Models\Contract;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Action;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuItem;
use WireElements\Pro\Concerns\InteractsWithConfirmationModal;

class ContractDatatable extends DataTableComponent
{
    use InteractsWithConfirmationModal;
    use WithCustomizations;

    protected $listeners = ['refresh' => '$refresh'];

    public function boot(): void
    {
        $this->authorize('viewAny', Contract::class);
    }

    public function configure(): void
    {
        $this->useDefaults();

        $this->setTitle("Contracts");
    }

    public function builder(): Builder
    {
        return Contract::query()->select('contracts.*');
    }

    public function columns(): array
    {
        return [
            Column::make("Title")->from(fn($row) => $row->sections->title),
            Column::make("Customer", "customer.name")->searchable(),
            Column::make("Amount")->from(fn($row) => money($row->getAmount())),
            Column::make("Ticket", 'ticket.number')->sortable()->searchable(),

            Column::make("Synced", 'ticket.number')
                ->label(fn($row) => $row->lexoffice_id
                    ? "<i class='fa fa-check-circle text-success'></i>"
                    : "<i class='fa fa-exclamation-circle text-warning'></i>"
                )
                ->html(),

            MenuColumn::make('Actions', 'actions')
                ->actions([
                    MenuItem::make('')
                        ->title(fn($row) => 'Get Document')
                        ->icon('fal fa-file-pdf me-1')
                        ->permission(fn($row) => user()->can('view', $row))
                        ->attributes(fn($row) => [
                            'wire:click' => "getDocument($row->id)"
                        ]),

                    MenuItem::make('')
                        ->title(fn($row) => 'Send To Lexoffice')
                        ->icon('fal fa-paper-plane me-1')
                        ->permission(fn($row) => user()->can('create', $row))
                        ->attributes(fn($row) => [
                            'wire:click' => "sendToLexoffice($row->id)"
                        ]),

                    MenuItem::make('')
                        ->title(fn($row) => 'Edit')
                        ->icon('fal fa-pen me-1')
                        ->permission(fn($row) => user()->can('update', $row))
                        ->wireElement(fn($row) => [
                            'type' => 'modal',
                            'component' => 'forms.contract-form',
                            'params' => ['contract' => $row->id]
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
                ->hideIf(user()->cannot('create', Contract::class))
                ->wireModal('forms.contract-form'),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Component Actions
    |--------------------------------------------------------------------------
    */

    public function delete(Contract $contract): void
    {
        $this->authorize('delete', $contract);

        $this->askForConfirmation(function () use ($contract){
            $contract->delete();
            $this->dispatch('flashNotification', message: __("Contract deleted"));
        });
    }

    public function sendToLexoffice(Contract $contract): void
    {
        $contract->sendToLexOffice();

        $this->dispatch('flashNotification', message: __('Contract updated'));
        $this->dispatch('refresh');
    }

    public function getDocument(Contract $contract): void
    {
        try {
            $document = $contract->getDocument();
        } catch (Exception $exception) {
            $this->dispatch(
                'flashNotification',
                heading: __('The document could not be fetched'),
                message: $exception->getMessage(),
                type: 'warning'
            );

            return;
        }

        if ($document) {
            $this->dispatch('modal.open', component: 'modals.document-viewer', arguments: ['document' => $document->id]);
        } else {
            $this->dispatch('flashNotification', message: __('The document could not be fetched'), type: 'warning');
        }
    }
}
