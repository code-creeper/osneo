<?php

namespace App\Livewire\Datatables;

use App\LaravelLivewireTables\Traits\WithCustomizations;
use App\Models\Constant;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Action;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuItem;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;
use WireElements\Pro\Concerns\InteractsWithConfirmationModal;

class ConstantDatatable extends DataTableComponent
{
    use InteractsWithConfirmationModal;
    use WithCustomizations;

    protected $listeners = ['refresh' => '$refresh'];

    public function boot(): void
    {
        $this->authorize('viewAny', Constant::class);
    }

    public function configure(): void
    {
        $this->useDefaults();

        $this->setTitle("Constants");
    }

    public function builder(): Builder
    {
        return Constant::query()->select('*');
    }

    public function columns(): array
    {
        return [
            Column::make("Group")->sortable(),
            Column::make("Key")->sortable(),
            Column::make("Value")->sortable(),

            MenuColumn::make('Actions', 'actions')
                ->actions([
                    MenuItem::make('')
                        ->title(fn($row) => 'Edit')
                        ->icon('fal fa-pen me-1')
                        ->permission(fn($row) => user()->can('update', $row))
                        ->wireElement(fn($row) => [
                            'type' => 'modal',
                            'component' => 'forms.constant-form',
                            'params' => ['constant' => $row->id]
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
        $groups = Constant::distinct('group')->pluck('group')->mapWithKeys(
            fn ($group) => [$group => str($group)->title()->replace('_' ,' ')]
        )->toArray();

        return [
            SelectFilter::make('Group')
                ->options((
                    ['' => 'All'] +
                    $groups
                ))
                ->filter(function (Builder $query, string $value) {
                    $query->whereGroup($value);
                }),
        ];
    }

    public function actions(): array
    {
        return [
            Action::make("Add New")
                ->primary(outline: true)
                ->icon('fal fa-plus')
                ->hideIf(user()->cannot('create', Constant::class))
                ->wireModal('forms.constant-form'),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Component Actions
    |--------------------------------------------------------------------------
    */

    public function delete(Constant $constant): void
    {
        $this->authorize('delete', $constant);

        $this->askForConfirmation(function () use ($constant){
            $constant->delete();
            $this->dispatch('flashNotification', message: __("Constant deleted"));
        });
    }
}
