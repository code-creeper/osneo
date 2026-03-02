<?php

namespace App\Livewire\Datatables;

use App\LaravelLivewireTables\Traits\WithCustomizations;
use App\LaravelLivewireTables\Views\Filters\Select2Filter;
use App\Models\Activity;
use App\Models\Attendance;
use App\Models\AttendanceSummary;
use App\Models\Leave;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\LinkColumn;
use Rappasoft\LaravelLivewireTables\Views\Filters\DateFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;
use WireElements\Pro\Concerns\InteractsWithConfirmationModal;

class ActivityDatatable extends DataTableComponent
{
    use InteractsWithConfirmationModal;
    use WithCustomizations;

    protected $listeners = ['refresh' => '$refresh'];

    public function boot(): void
    {
        $this->authorize('viewAny', AttendanceSummary::class);
    }

    public function builder(): Builder
    {
        return Activity::query()
            ->with('causer')
            ->select('*');
    }

    public function configure(): void
    {
        $this->useDefaults();

        $this->setTitle("Activity Log");
        $this->setSearchDisabled();
    }

    public function columns(): array
    {
        return [
            Column::make("User")->from(
                fn($row) => "<p class='mb-1'>
                   {$row->causer->name} <br>
                   <span class='text-muted small'>{$row->role}</span>
                  </p>"
            )->html(),

            Column::make("Description"),
            Column::make("URL")->from(fn($row) => $row->getExtraProperty('url')),

            LinkColumn::make('Details')
                ->title(fn($row) => $row->isCrud() ? 'Details' : '-')
                ->location(fn($row) => '#')
                ->attributes(fn($row) => ['class' => $row->isCrud() ? 'text-info' : ''])
                ->wireElement(fn($row) => $row->isCrud() ? [
                    'type' => 'modal',
                    'component' => 'modals.activity-details',
                    'params' => ['activity' => $row->id]
                ] : []),

            Column::make("Logged At", "created_at")->format(fn($value) => $value->date())->sortable(),
        ];
    }

    public function filters(): array
    {
        $logTypes = Activity::getLogTypes();
        $modules = Activity::whereNotNull('subject_type')->groupBy('subject_type')->pluck('subject_type')->mapWithKeys(
            fn ($module) => [$module => class_basename($module)]
        )->toArray();
        $users = User::relevant()->oldest('first_name')->get()->toKeyValuePair();

        return [
            SelectFilter::make('Log Type')
                ->options($logTypes)
                ->setFirstOption()
                ->filter(function (Builder $query, string $value) {
                    $query->inLog($value);
                }),

            Select2Filter::make('User')
                ->options($users)
                ->setFirstOption()
                ->filter(function (Builder $query, string $value) {
                    $query->causedBy(User::find($value));
                }),

            Select2Filter::make('Affected User', 'affected_user')
                ->options($users)
                ->setFirstOption()
                ->filter(function (Builder $query, string $value) {
                    $module = $this->getAppliedFilterWithValue('module');
                    $affectedUser = $value;

                    $query->getQuery()
                        ->when(
                            $module == Leave::class,
                            fn($query) => $query->whereExists(fn($subQuery) => $subQuery->select(DB::raw(1))
                                ->from('leaves')
                                ->whereColumn('activity_log.subject_id', 'leaves.id')
                                ->where('leaves.user_id', $affectedUser))
                        )
                        ->when(
                            $module == Attendance::class,
                            fn($query) => $query->whereExists(fn($subQuery) => $subQuery->select(DB::raw(1))
                                ->from('attendances')
                                ->where('activity_log.subject_id', 'leaves.id')
                                ->where('attendances.user_id', $affectedUser)
                                ->whereNull('attendances.deleted_at')
                            )
                        );
                }),

            SelectFilter::make('Select Module', 'module')
                ->options($modules)
                ->setFirstOption()
                ->filter(function (Builder $query, string $value) {
                    $query->whereSubjectType($value);
                }),

            DateFilter::make('Select Date')
                ->setFilterDefaultValue(now()->toDateString())
                ->filter(function (Builder $query, string $value) {
                    $query->whereDate('created_at', $value);
                }),
        ];
    }
}
