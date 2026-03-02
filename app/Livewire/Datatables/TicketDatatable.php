<?php

namespace App\Livewire\Datatables;

use App\LaravelLivewireTables\Traits\WithCustomizations;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Action;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuItem;
use WireElements\Pro\Concerns\InteractsWithConfirmationModal;

class TicketDatatable extends DataTableComponent
{
    use InteractsWithConfirmationModal;
    use WithCustomizations;

    protected $listeners = ['refresh' => '$refresh'];

    public function boot(): void
    {
        $this->authorize('viewAny', Ticket::class);
    }

    public function configure(): void
    {
        $this->useDefaults();
        $this->setTitle("Tickets");
    }

    public function builder(): Builder
    {
        return Ticket::query()
            ->select('tickets.*')
            ->withCount('documents');
    }

    public function columns(): array
    {
        return [
            Column::make("Number")->sortable(),
            Column::make("Documents Count")->from(fn($row) => $row->documents_count)->sortable(),

            MenuColumn::make('Actions', 'actions')
                ->actions([
                    MenuItem::make('')
                        ->title(fn($row) => 'Merge & Delete')
                        ->permission(fn($row) => user()->can('update', $row))
                        ->icon('fal fa-code-branch me-1')
                        ->wireElement(fn($row) => [
                            'type' => 'modal',
                            'component' => 'modals.merge-ticket',
                            'params' => ['ticket' => $row->id]
                        ]),

                    MenuItem::make('')
                        ->title(fn($row) => 'Edit')
                        ->icon('fal fa-pen me-1')
                        ->permission(fn($row) => user()->can('update', $row))
                        ->wireElement(fn($row) => [
                            'type' => 'modal',
                            'component' => 'forms.ticket-form',
                            'params' => ['ticket' => $row->id]
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
                ->hideIf(user()->cannot('create', Ticket::class))
                ->wireModal('forms.ticket-form'),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Component Actions
    |--------------------------------------------------------------------------
    */

    public function delete(Ticket $ticket): void
    {
        $this->authorize('delete', $ticket);

        $this->askForConfirmation(function () use ($ticket){
            $ticket->delete();
            $this->dispatch('flashNotification', message: __("Ticket deleted successfully"));
        });
    }
}
