<?php

namespace App\Livewire\Datatables;

use App\LaravelLivewireTables\Traits\WithCustomizations;
use App\LaravelLivewireTables\Views\Filters\Select2Filter;
use App\Livewire\Traits\InteractsWithConfirmationModal;
use App\Models\LeaveTransaction;
use App\Models\User;
use App\Notifications\LeaveBalanceAdjusted;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuItem;
use Rappasoft\LaravelLivewireTables\Views\Filters\MonthFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class LeaveTransactionDatatable extends DataTableComponent
{
    use InteractsWithConfirmationModal;
    use WithCustomizations;

    protected $listeners = ['refresh' => '$refresh'];

    public function boot(): void
    {
        $this->authorize('viewAny', LeaveTransaction::class);
    }

    public function configure(): void
    {
        $this->useDefaults();

        $this->setTitle("Leave Transactions History");

        $this->setSearchDisabled();
    }

    public function builder(): Builder
    {
        return LeaveTransaction::query()
            ->relevant()
            ->select('leave_transactions.*')
            ->with('transactor', 'user');
    }

    public function columns(): array
    {
        return [
            Column::make("User")
                ->hideIf(user()->cannot('view all leaves'))
                ->from(fn($row) => $row->user?->name)->sortable(),

            Column::make("Amount")->sortable(),
            Column::make("Comments")->sortable(),
            Column::make("Transacted By")->from(fn($row) => $row->transactor?->name),
            Column::make("Transacted On")->format(fn($value) => $value->date())->sortable(),

            MenuColumn::make('Actions', 'actions')
                ->actions([
                    MenuItem::make('')
                        ->title(fn($row) => 'Edit')
                        ->permission(fn($row) => user()->can('update', $row))
                        ->icon('fal fa-pen me-1')
                        ->wireElement(fn($row) => [
                            'type' => 'modal',
                            'component' => 'forms.leave-transaction-form',
                            'params' => ['user' => $row->user_id, 'leaveTransaction' => $row->id]
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
        return [
            MonthFilter::make('Month')
                ->setFilterDefaultValue(now()->format('Y-m'))
                ->filter(function (Builder $query, string $value) {
                    $query->whereDateFormat('transacted_on', $value, 'Y-m');
                }),

            Select2Filter::make('User')
                ->options(User::whereHas('leaveTransactions')->orderBy('first_name')->get()->toKeyValuePair())
                ->setFirstOption()
                ->hideIf(user()->cannot('view all leaves'))
                ->filter(function (Builder $query, string $value) {
                    $query->whereUserId($value);
                }),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Component Actions
    |--------------------------------------------------------------------------
    */

    public function delete(LeaveTransaction $leaveTransaction): void
    {
        $this->authorize('delete', $leaveTransaction);

        $this->askForConfirmation(function ($comments) use ($leaveTransaction){
            $user = $leaveTransaction->user;

            $transaction = $user->createLeaveTransaction([
                'transacted_by' => auth()->id(),
                'amount' => 0 - $leaveTransaction->amount,
                'comments' => $comments ?: "Transaction reversed. Trx ID: $leaveTransaction->id",
            ]);

            $user->notify(new LeaveBalanceAdjusted($transaction));

            $this->dispatch('flashNotification', message: __("Leave balance adjusted successfully"));
        },
            prompt: ['message' => ''],
            confirmWithComments: true,
        );
    }
}
