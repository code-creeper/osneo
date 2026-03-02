<?php

namespace App\Livewire\Datatables;

use App\LaravelLivewireTables\Traits\WithCustomizations;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;

class LeaveBalanceDatatable extends DataTableComponent
{
    use WithCustomizations;

    protected $listeners = ['refresh' => '$refresh'];

    public $date;

    public function boot(): void
    {
        $this->authorize('access admin area');
    }

    public function configure(): void
    {
        $this->useDefaults();
        $this->setTitle("Leaves Balance");
    }

    public function builder(): Builder
    {
        $date = ($this->date ? Carbon::createFromFormat(config('dates.defaults.date'), $this->date) : now())->toDateString();

        return User::query()
            ->withCount([
                'leaveDays as upcoming_leaves' => fn($builder) => $builder->future('date', $date)->paid(),
            ])
            ->select(
                DB::raw("(SELECT COALESCE(SUM(amount), 0) FROM leave_transactions WHERE user_id = users.id and date(created_at) < '$date') as available_leaves"),
                'users.*'
            )
            ->relevant();
    }

    public function columns(): array
    {
        return [
            Column::make("User")->from(fn($row) => $row->name),
            Column::make("Available Leaves")->from(fn($row) => $row->available_leaves),
            Column::make("Upcoming Leaves")->from(fn($row) => $row->upcoming_leaves),
        ];
    }
}
