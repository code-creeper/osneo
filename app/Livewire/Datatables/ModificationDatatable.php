<?php

namespace App\Livewire\Datatables;

use App\Enums\ModificationType;
use App\LaravelLivewireTables\Traits\WithCustomizations;
use App\Models\Modification;
use App\Notifications\ChangesApproved;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\LinkColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuItem;
use WireElements\Pro\Concerns\InteractsWithConfirmationModal;

class ModificationDatatable extends DataTableComponent
{
    use InteractsWithConfirmationModal;
    use WithCustomizations;

    protected $listeners = ['refresh' => '$refresh'];

    public function boot(): void
    {
        $this->authorize('viewAny', Modification::class);
    }

    public function configure(): void
    {
        $this->useDefaults();
        $this->setTitle("Modification Requests");

        $this->setSearchDisabled();
        $this->setColumnSelectDisabled();
        $this->setPaginationDisabled();
    }

    public function builder(): Builder
    {
        return Modification::query()
            ->select('modifications.*')
            ->ofActiveUsers()->relevant()->pending()
            ->with('user');
    }

    public function columns(): array
    {
        return [
            Column::make("Module", 'modifiable_type')->format(fn($value) => class_basename($value)),

            Column::make("Modification Type", 'type')
                ->format(
                    fn($modificationType) => "<span class='text-". $modificationType->color() ."'>$modificationType->name</span>"
                )->html(),
            Column::make("Employee")->from(fn($row) => $row->user->name),

            LinkColumn::make('Details')
                ->title(fn($row) => $row->type == ModificationType::Edit ? __(':count Changes', ['count' => $row->changes_count]) : "Show Details")
                ->location(fn($row) => '#')
                ->attributes(fn($row) => ['class' => 'text-info'])
                ->wireElement(fn($row) => [
                    'type' => 'modal',
                    'component' => 'modals.modification-details',
                    'params' => ['modification' => $row->id]
                ]),

            Column::make("Comments"),

            MenuColumn::make('Actions', 'actions')
                ->actions([
                    MenuItem::make('')
                        ->title(fn($row) => 'Approve')
                        ->icon('fal fa-check me-1')
                        ->permission(fn($row) => user()->can('update', $row))
                        ->attributes(fn($row) => [
                            'class' => 'text-success',
                            'wire:click' => "approve($row->id)"
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

    /*
    |--------------------------------------------------------------------------
    | Component Actions
    |--------------------------------------------------------------------------
    */

    public function delete(Modification $modification): void
    {
        $this->authorize('delete', $modification);

        $this->askForConfirmation(function () use ($modification){
            $modification->delete();
            $this->dispatch('flashNotification', message: __("Modification request declined"));
        });
    }

    public function approve(Modification $modification): void
    {
        $modification->update([
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);

        if ($modification->type == ModificationType::Create){
            $modification->getNewModifiable()->applyCreation($modification);
        }

        if ($modification->type == ModificationType::Edit){
            $modification->modifiable->applyChanges($modification);
        }

        if ($modification->type == ModificationType::Delete){
            $modification->modifiable->applyDeletion();
        }

        if ($modification->type == ModificationType::Restore){
            $modification->modifiable->applyRestoration();
        }

        $modification->user->notify(new ChangesApproved($modification));

        $this->dispatch('flashNotification', message: __("Modification request approved"));
    }
}
