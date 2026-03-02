<?php

namespace App\Livewire\Datatables;

use App\LaravelLivewireTables\Traits\WithCustomizations;
use App\LaravelLivewireTables\Views\Filters\Select2Filter;
use App\Models\ManualEntry;
use App\Models\User;
use App\Notifications\ManualEntryNotification;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Action;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuItem;
use Rappasoft\LaravelLivewireTables\Views\Filters\MonthFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;
use WireElements\Pro\Concerns\InteractsWithConfirmationModal;

class ManualEntryDatatable extends DataTableComponent
{
    use InteractsWithConfirmationModal;
    use WithCustomizations;

    protected $listeners = ['refresh' => '$refresh'];

    public function boot(): void
    {
        $this->authorize('viewAny', ManualEntry::class);
    }

    public function configure(): void
    {
        $this->useDefaults();
        $this->setTitle("Manual Entries");

        $this->setSearchDisabled();
    }

    public function builder(): Builder
    {
        return ManualEntry::query()
            ->select('manual_entries.*')
            ->with(['user', 'admin']);
    }

    public function columns(): array
    {
        return [
            Column::make("User")->from(fn($row) => $row->user?->name),
            Column::make("Date")->format(fn($date) => $date->date()),
            Column::make("Duration")->format(fn($value) => formatMins($value, true))->sortable(),
            Column::make("Logged By")->from(fn($row) => $row->admin?->name),
            Column::make("Comments"),
            Column::make("Payout")
                ->format(fn($payout) => $payout ?
                    "<span class='badge badge-success-lighten px-2'>
                        <i class='fal fa-exclamation-circle'></i>
                        ".__('Payout')."
                    </span>"
                    : '')
                ->sortable()->html(),

            MenuColumn::make('Actions', 'actions')
                ->actions([
                    MenuItem::make('')
                        ->title(fn($row) => 'Edit')
                        ->icon('fal fa-pen me-1')
                        ->permission(fn($row) => user()->can('update', $row))
                        ->wireElement(fn($row) => [
                            'type' => 'modal',
                            'component' => 'forms.manual-entry-form',
                            'params' => ['entry' => $row->id]
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
        $users = User::whereHas('manualEntries')->orderBy('first_name')->get()->toKeyValuePair();

        return [
            MonthFilter::make('Month')
                ->setFilterDefaultValue(now()->format('Y-m'))
                ->filter(function (Builder $query, string $value) {
                    $query->whereDateFormat('date', $value, 'Y-m');
                }),

            Select2Filter::make('User')
                ->options($users)
                ->setFirstOption()
                ->filter(function (Builder $query, string $value) {
                    $query->whereUserId($value);
                }),
        ];
    }

    public function actions(): array
    {
        return [
            Action::make("Add New")
                ->primary(outline: true)
                ->icon('fal fa-plus')
                ->hideIf(user()->cannot('create', ManualEntry::class))
                ->wireModal('forms.manual-entry-form'),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Component Actions
    |--------------------------------------------------------------------------
    */

    public function delete(ManualEntry $entry): void
    {
        $this->authorize('delete', $entry);

        $this->askForConfirmation(function () use ($entry){
            $entry->delete();

            if ($entry->user_id !== auth()->id()){
                $entry->user->notify(new ManualEntryNotification($entry));
            }

            $this->dispatch('flashNotification', message: __("Entry deleted"));
        });
    }
}
