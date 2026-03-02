<?php

namespace App\Livewire\Datatables;

use App\LaravelLivewireTables\Traits\WithCustomizations;
use App\Models\LeaveReason;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Action;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\BooleanColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\ColorColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuItem;
use WireElements\Pro\Concerns\InteractsWithConfirmationModal;

class LeaveReasonDatatable extends DataTableComponent
{
    use InteractsWithConfirmationModal;
    use WithCustomizations;

    protected $listeners = ['refresh' => '$refresh'];

    public function boot(): void
    {
        $this->authorize('viewAny', LeaveReason::class);
    }

    public function configure(): void
    {
        $this->useDefaults();
        $this->setTitle("Leave Reasons");

        $this->setSearchDisabled();
        $this->setColumnSelectDisabled();
        $this->setPaginationDisabled();
    }

    public function builder(): Builder
    {
        return LeaveReason::query()->select('leave_reasons.*');
    }

    public function columns(): array
    {
        return [
            Column::make("Name")->sortable(),
            ColorColumn::make("Color", 'color')
                ->attributes(fn($row) => [
                    "style" => "width:50px; height: 20px; background: $row->color"
                ]),

            BooleanColumn::make("Paid")->yesNo(),
            BooleanColumn::make("Deductible")->yesNo(),

            MenuColumn::make('Actions', 'actions')
                ->actions([
                    MenuItem::make('')
                        ->title(fn($row) => 'Edit')
                        ->icon('fal fa-pen me-1')
                        ->permission(fn($row) => user()->can('update', $row))
                        ->wireElement(fn($row) => [
                            'type' => 'modal',
                            'component' => 'forms.leaveReason-form',
                            'params' => ['leaveReason' => $row->id]
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
                ->hideIf(user()->cannot('create', LeaveReason::class))
                ->wireModal('forms.leaveReason-form'),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Component Actions
    |--------------------------------------------------------------------------
    */

    public function delete(LeaveReason $leaveReason): void
    {
        $this->authorize('delete', $leaveReason);

        $this->askForConfirmation(function () use ($leaveReason){
            $leaveReason->delete();
            $this->dispatch('flashNotification', message: __("Leave Reason deleted"));
        });
    }
}
