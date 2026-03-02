<?php

namespace App\Livewire\Datatables;

use App\LaravelLivewireTables\Traits\WithCustomizations;
use App\LaravelLivewireTables\Views\Filters\Select2Filter;
use App\Models\AttendanceSummary;
use App\Models\User;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Action;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\BooleanColumn;
use Rappasoft\LaravelLivewireTables\Views\Filters\MonthFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;
use WireElements\Pro\Concerns\InteractsWithConfirmationModal;

class AttendanceSummariesDatatable extends DataTableComponent
{
    use InteractsWithConfirmationModal;
    use WithCustomizations;

    protected $listeners = ['refresh' => '$refresh'];

    public function boot(): void
    {
        $this->authorize('viewAny', AttendanceSummary::class);
    }

    public function configure(): void
    {
        $this->useDefaults();
        $this->setTitle("Attendance Summary");

        $this->defaultSortColumn = 'date';
        $this->defaultSortDirection = 'asc';

        $this->setTrAttributes(function ($row, $index) {
            $result = ['default' => true];

            if ($row->off_day) {
                $result['class'] = 'table-active';
            } elseif ($row->leave) {
                $result['class'] = 'table-active table-light text-white';
            } elseif ($row->holiday) {
                $result['class'] = 'table-active table-success text-white';
            }

            return $result;
        });
    }

    public function builder(): Builder
    {
        return AttendanceSummary::query()
            ->select([
                'attendance_summaries.*',
                DB::raw("CONCAT(users.first_name, ' ', COALESCE(users.last_name, '')) AS user_name"),
            ])
            ->joinRelationship('user', fn($join) => $join->where('active', 1))
            ->whereNotIn('users.id', [config('app.system_user_id')]);
    }

    public function columns(): array
    {
        return [
            Column::make("User")
                ->from(fn($row) => $row->user_name)
                ->hideIf(user()->cannot('view all attendance'))
                ->sortable(),
            Column::make("Date")->format(fn($value) => $value->date())->sortable(),
            Column::make("Target Time")->format(fn($value) => minutesToDurationInput($value))->sortable(),
            Column::make("Working Time")->format(fn($value) => minutesToDurationInput($value))->sortable(),
            Column::make("Paid Time")->format(fn($value) => minutesToDurationInput($value))->sortable(),
            Column::make("Manual Time")->format(fn($value) => minutesToDurationInput($value))->sortable(),
            Column::make("Payout Time")->format(fn($value) => minutesToDurationInput($value))->sortable(),
            Column::make("Overtime")->format(fn($value) => minutesToDurationInput($value))->sortable(),
            BooleanColumn::make("Leave")->yesNo()->sortable(),
            BooleanColumn::make("Off Day")->yesNo()->sortable(),
            BooleanColumn::make("Holiday")->yesNo()->sortable(),
        ];
    }

    public function filters(): array
    {
        $users = User::relevant()->orderBy('first_name')->get()->toKeyValuePair();
        return [
            MonthFilter::make('Month')
                ->setFilterDefaultValue(now()->format('Y-m'))
                ->filter(function (Builder $query, string $value) {
                    $query->whereDateFormat('date', $value, 'Y-m');
                }),

            Select2Filter::make('User')
                ->hideIf(user()->cannot('view all attendance'))
                ->setFirstOption()
                ->options($users)
                ->filter(function (Builder $query, string $value) {
                    $query->whereUserId($value);
                }),
        ];
    }

    public function actions(): array
    {
        return [
            Action::make("Update Summaries")
                ->primary()
                ->hideIf(user()->cannot('access admin area'))
                ->wireModal('modals.update-attendance-summaries'),
        ];
    }
}
