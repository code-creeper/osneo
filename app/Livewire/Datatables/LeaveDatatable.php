<?php

namespace App\Livewire\Datatables;

use App\Enums\LeaveAction;
use App\Enums\ModificationType;
use App\LaravelLivewireTables\Traits\WithCustomizations;
use App\LaravelLivewireTables\Views\Filters\Select2Filter;
use App\Livewire\Traits\InteractsWithConfirmationModal;
use App\Models\Leave;
use App\Models\LeaveReason;
use App\Models\User;
use App\Notifications\LeaveActionTaken;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Action;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\LinkColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuItem;
use Rappasoft\LaravelLivewireTables\Views\Filters\MonthFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class LeaveDatatable extends DataTableComponent
{
    use InteractsWithConfirmationModal;
    use WithCustomizations;

    protected $listeners = ['refresh' => '$refresh'];

    public function boot(): void
    {
        $this->authorize('viewAny', Leave::class);
    }

    public function configure(): void
    {
        $this->setTitle('Leaves');

        $this->useDefaults();
    }

    public function builder(): Builder
    {
        return Leave::query()
            ->ofActiveUsers()
            ->relevant()
            ->with(['reason', 'user', 'tags', 'claim', 'modifications', 'pendingModification'])
            ->select('leaves.*');
    }

    public function columns(): array
    {
        return [
            Column::make("User")->from(fn($row) => $row->user?->name)
                ->hideIf(user()->cannot('view all leaves'))
                ->sortable(),

            Column::make("Applied On")
                ->from(fn($row) => $row->created_at->date())
                ->sortable(fn(Builder $query) => $query->orderBy('created_at')),

            Column::make("Starts On")
                ->from(fn($row) => $row->starts_on->date())
                ->sortable(fn(Builder $query) => $query->orderBy('starts_on')),

            Column::make("Ends On")
                ->from(fn($row) => $row->ends_on->date())
                ->sortable(fn(Builder $query) => $query->orderBy('ends_on')),

            Column::make("Days")->sortable(),
            Column::make("Reason")->from(fn($row) => $row->reason?->name)->sortable(),
            Column::make("Status")->from(fn($row) => $row->status)->sortable(),

            Column::make("Tags")->from(function ($row) {
                if (!$row->tags->count()){
                    return "-";
                }

                $value = "";

                foreach ($row->tags as $tag) {
                    $value .= "<span class='badge me-1' style='background-color: $tag->color'>$tag->name</span>";
                }

                return $value;
            })->html(),

            LinkColumn::make('History')
                ->title(fn($row) => $row->activities->count() ? "<i class='fal fa-history'></i>" : '')
                ->location(fn($row) => '#')
                ->attributes(fn($row) => ['class' => 'text-info'])
                ->wireElement(fn($row) => $row->activities->count() ? [
                    'type' => 'slide-over',
                    'component' => 'modals.changes-history-modal',
                    'params' => ['modelType' => Leave::class, 'modelId' => $row->id]
                ] : [])
                ->html(),

            MenuColumn::make('Actions')
                ->actions([
                    MenuItem::make('')
                        ->title(fn($row) => 'Edit')
                        ->permission(fn($row) => user()->can('update', $row))
                        ->icon('fal fa-pen mr-1')
                        ->wireElement(fn($row) => [
                            'type' => 'modal',
                            'component' => 'forms.leave-form',
                            'params' => ['leave' => $row->id]
                        ]),

                    MenuItem::make('')
                        ->title(fn($row) => 'Approve')
                        ->icon('fal fa-check me-1')
                        ->permission(fn($row) => user()->can('approve', $row))
                        ->attributes(fn($row) => [
                            'wire:click' => "approve($row->id)"
                        ]),

                    MenuItem::make('')
                        ->title(fn($row) => 'Reject')
                        ->icon('fal fa-times me-1')
                        ->permission(fn($row) => user()->can('reject', $row))
                        ->attributes(fn($row) => [
                            'wire:click' => "reject($row->id)"
                        ]),

                    MenuItem::make('')
                        ->title(fn($row) => 'Insurance Claim')
                        ->icon('fal fa-comment-edit mr-1')
                        ->permission(fn($row) => user()->can('processClaim', $row))
                        ->wireElement(fn($row) => [
                            'type' => 'modal',
                            'component' => 'modals.process-insurance-claim',
                            'params' => ['leave' => $row->id]
                        ]),

                    MenuItem::make('')
                        ->title(fn($row) => 'Delete')
                        ->icon('fa fa-trash me-1')
                        ->permission(fn($row) => user()->can('delete', $row))
                        ->attributes(fn($row) => [
                            'class' => 'text-danger',
                            'wire:click' => "delete($row->id)"
                        ]),
                ]),
        ];
    }

    public function filters(): array
    {
        $users = User::whereHas('leaves')->orderBy('first_name')->get()->toKeyValuePair();

        return [
            MonthFilter::make('Month')
                ->setFilterDefaultValue(now()->format('Y-m'))
                ->filter(function (Builder $query, string $value) {
                    $date = Carbon::parse($value);
                    $query->forMonth($date->month, $date->year);
                }),

            SelectFilter::make('Reason')
                ->options(LeaveReason::all()->toKeyValuePair())
                ->setFirstOption()
                ->filter(function (Builder $query, string $value) {
                    $query->where('reason_id', $value);
                }),

            Select2Filter::make('User')
                ->options($users)
                ->setFirstOption()
                ->hideIf(user()->cannot('view all leaves'))
                ->filter(function (Builder $query, string $value) {
                    $query->whereUserId($value);
                }),

            SelectFilter::make('Status')
                ->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ])
                ->setFirstOption()
                ->filter(function (Builder $query, string $value) {
                    $query->status($value);
                }),
        ];
    }

    public function actions(): array
    {
        return [
            Action::make("Add New")
                ->primary(outline: true)
                ->icon('fal fa-plus')
                ->hideIf(user()->cannot('create', Leave::class))
                ->wireModal('forms.leave-form'),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Component Actions
    |--------------------------------------------------------------------------
    */

    public function delete(Leave $leave): void
    {
        $this->authorize('delete', $leave);

        $confirmWithComments = user()->can('delete leaves without approval');

        $this->askForConfirmation(function ($comments) use ($leave, $confirmWithComments){
            if (user()->can('delete leaves without approval')) {
                $leave->delete();

                if (user()->id !== $leave->user_id) {
                    $leave->user->notify(new LeaveActionTaken($leave, LeaveAction::Deleted));
                }

                $this->dispatch('flashNotification', message: __("Leave deleted successfully"));
                return;
            }

            $leave->createModification(['comments' => $comments], ModificationType::Delete);

            $this->dispatch('flashNotification', message: __("Request for deletion has been sent to admin"));
        },
            prompt: ['message' => $confirmWithComments ? '' : __('Are you sure you want to perform this action ?')],
            confirmWithComments: $confirmWithComments,
            commentRules: $confirmWithComments ? 'required' : '',
        );
    }

    public function approve(Leave $leave): void
    {
        $this->authorize('approve', $leave);
        $leave->approve();
        $leave->user->notify(new LeaveActionTaken($leave, LeaveAction::Approved));

        $this->dispatch('flashNotification', message: __('Leave approved successfully'));
    }

    public function reject(Leave $leave): void
    {
        $this->authorize('reject', $leave);
        $leave->reject();
        $leave->user->notify(new LeaveActionTaken($leave, LeaveAction::Rejected));
        $this->dispatch('flashNotification', message: __('Leave rejected successfully'));
    }
}
