<?php

namespace App\Livewire\Datatables;

use App\Enums\AttendanceAction;
use App\Enums\ModificationType;
use App\LaravelLivewireTables\Traits\WithCustomizations;
use App\LaravelLivewireTables\Views\Filters\Select2Filter;
use App\Livewire\Traits\InteractsWithConfirmationModal;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\User;
use App\Notifications\AttendanceActionTaken;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Action;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\LinkColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuItem;
use Rappasoft\LaravelLivewireTables\Views\Filters\MonthFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;

class AttendanceDatatable extends DataTableComponent
{
    use InteractsWithConfirmationModal;
    use WithCustomizations;

    protected $listeners = ['refresh' => '$refresh'];

    public function boot(): void
    {
        $this->authorize('viewAny', Attendance::class);
    }

    public function configure(): void
    {
        $this->useDefaults();

        $this->setTitle("Attendance Overview");

        $this->setSearchDisabled();

        $this->setConfigurableAreas([
            'after-toolbar' => [
                'includes.datatable.attendance.pending-attendance-notice', [
                    'pendingAttendanceCount' => user()->pendingAttendances()->count(),
                ],
            ],
        ]);

        $this->setTrAttributes(function($row) {
            if ($row->deleted_at) {
                return [
                    'class' => 'text-danger text-decoration-line-through',
                ];
            }

            return [];
        });
    }

    public function builder(): Builder
    {
        return Attendance::query()
            ->join('users', 'users.id', '=', 'attendances.user_id')
            ->with(['pendingModification'])
            ->select(
                'attendances.*',
                DB::raw("CONCAT(users.first_name, ' ', COALESCE(users.last_name, '')) AS user_name")
            )
            ->where('users.active', 1)
            ->withTrashed()
            ->relevant();
    }

    public function columns(): array
    {
        return [
            Column::make("User")
                ->from(fn($row) => $row->user_name)
                ->hideIf(user()->cannot('view all attendance'))
                ->sortable(),
            Column::make("Date")->format(fn($value) => $value->date())->sortable(),
            Column::make("Checkin", 'checkin')
                ->format(
                    fn($value) => $value?->format(config('dates.attendance.time'))
                )
                ->sortable(),

            Column::make("Checkout")
                ->format(
                    fn($value) => $value?->format(config('dates.attendance.time'))
                )
                ->sortable(),

            Column::make("Duration")->format(fn($value) => formatMins($value, true))->sortable(),

            LinkColumn::make('History')
                ->title(fn($row) => $row->activities->count() ? "<i class='fal fa-history'></i>" : '')
                ->location(fn($row) => '#')
                ->attributes(fn($row) => ['class' => 'text-info'])
                ->wireElement(fn($row) => $row->activities->count() ? [
                    'type' => 'slide-over',
                    'component' => 'modals.changes-history-modal',
                    'params' => ['modelType' => Attendance::class, 'modelId' => $row->id]
                ] : [])
                ->html(),

            MenuColumn::make('Actions', 'actions')
                ->title(fn($row) => $row->pendingModification
                    ? "<i class='me-3'>".__('Requested approval for :action', ['action' => __($row->pendingModification->type->value)])."</i>"
                    : ''
                )
                ->actions([
                    MenuItem::make('')
                        ->title(fn($row) => 'Edit')
                        ->icon('fal fa-pen me-1')
                        ->permission(fn($row) => user()->can('update', $row))
                        ->wireElement(fn($row) => [
                            'type' => 'modal',
                            'component' => 'forms.attendance-form',
                            'params' => ['attendance' => $row->id]
                        ]),

                    MenuItem::make('')
                        ->title(fn($row) => 'Delete')
                        ->icon('fal fa-trash me-1')
                        ->permission(fn($row) => user()->can('delete', $row))
                        ->attributes(fn($row) => [
                            'class' => 'text-danger',
                            'wire:click' => "delete($row->id)",
                        ]),

                    MenuItem::make('')
                        ->title(fn($row) => 'Restore')
                        ->icon('fal fa-trash-undo me-1')
                        ->permission(fn($row) => user()->can('restore', $row))
                        ->attributes(fn($row) => [
                            'wire:click' => "restore($row->id)",
                        ]),

                    MenuItem::make('')
                        ->title(fn($row) => 'Cancel Modification Request')
                        ->icon('fal fa-trash-undo me-1')
                        ->permission(fn($row) => user()->can('deleteModification', $row))
                        ->attributes(fn($row) => [
                            'wire:click' => "deleteModification($row->id)",
                        ]),
                ]),
        ];
    }

    public function filters(): array
    {
        $users = User::relevant()->oldest('first_name')->get()->toKeyValuePair();

        return [
            MonthFilter::make('Month')
                ->setFilterDefaultValue(now()->format('Y-m'))
                ->filter(function (Builder $query, string $value) {
                    $query->whereDateFormat('date', $value, 'Y-m');
                }),

            Select2Filter::make('User')
                ->options($users)
                ->setFirstOption()
                ->hideIf(user()->cannot('view all attendance'))
                ->filter(function (Builder $query, $userId) {
                    $query->whereUserId($userId);
                }),
        ];
    }

    public function actions(): array
    {
        return [
            Action::make("Add New")
                ->primary(outline: true)
                ->icon('fal fa-plus')
                ->hideIf(user()->cannot('storeManually', Attendance::class))
                ->wireModal('forms.attendance-form'),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Component Actions
    |--------------------------------------------------------------------------
    */

    public function delete(Attendance $attendance): void
    {
        $this->authorize('delete', $attendance);

        if (user()->can('delete attendance without approval')){
            $attendance->delete();

            if ($attendance->user_id !== auth()->id()){
                $attendance->user->notify(new AttendanceActionTaken($attendance, AttendanceAction::Deleted));
            }

            $this->dispatch('flashNotification', message: ("Attendance deleted successfully"));
            return;
        }

        $this->askForConfirmation(function ($comments) use ($attendance) {
            $attendance->createModification(['comments' => $comments], ModificationType::Delete);

            $this->dispatch('flashNotification', message: __("Request for deletion has been sent to admin"));
        },
            prompt: ['message' => ''],
            confirmWithComments: true,
            commentRules: 'required',
        );
    }

    public function restore($attendanceId): void
    {
        $attendance = Attendance::withTrashed()->findOrFail($attendanceId);

        $this->authorize('restore', $attendance);

        if (user()->cannot('restore attendance without approval')) {
            $attendance->createModification([], ModificationType::Restore);
            $this->dispatch('flashNotification', message: __('Request for restoration has been sent to admin'));
            return;
        }

        $attendance->restore();

        if ($attendance->user_id !== auth()->id()) {
            $attendance->user->notify(new AttendanceActionTaken($attendance, AttendanceAction::Restored));
        }

        $this->dispatch('flashNotification', message: __('Attendance restored successfully'));
    }

    public function deleteModification($attendanceId): void
    {
        $attendance = Attendance::withTrashed()->findOrFail($attendanceId);
        $this->authorize('deleteModification', $attendance);

        $modification = $attendance->modifications()->pending()->firstOrFail();
        $modification->delete();

        $this->dispatch('flashNotification', message: __('Modification request has been cancelled successfully'));
    }
}
