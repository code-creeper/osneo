<?php

namespace App\Livewire\Datatables;

use App\LaravelLivewireTables\Traits\WithCustomizations;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Action;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuItem;
use Rappasoft\LaravelLivewireTables\Views\Filters\MonthFilter;

class PayrollDatatable extends DataTableComponent
{
    use WithCustomizations;

    protected $listeners = ['refresh' => '$refresh'];

    public function boot(): void
    {
        $this->authorize('viewAny', Payroll::class);
    }

    public function configure(): void
    {
        $this->useDefaults();

        $this->setTitle("Payrolls");
        $this->setFilterLayoutSlideDown();
        $this->setFilterSlideDownDefaultStatusEnabled();
    }

    public function builder(): Builder
    {
        return Payroll::query()
            ->with('user')
            ->select('payrolls.*');
    }

    public function columns(): array
    {
        return [
            Column::make("User")
                ->from(fn($row) => $row->user->name)
                ->searchable(
                    fn(Builder $builder, $search) => $builder
                        ->orWhereRelationLike('user', 'first_name', trim($search))
                        ->orWhereRelationLike('user', 'last_name', trim($search))
                ),

            Column::make("Status")->from(fn($row) => $row->status->label()),

            MenuColumn::make('Actions', 'actions')
                ->actions([
                    MenuItem::make('')
                        ->title(fn($row) => 'Process')
                        ->icon('fal fa-pencil me-1')
                        ->permission(fn($row) => user()->can('update', $row))
                        ->location(fn($row) => route('payroll.manage', $row)),
                ]),
        ];
    }

    public function filters(): array
    {
        return [
            MonthFilter::make('Month')
                ->setFilterDefaultValue(Carbon::parse(Payroll::latest()->value('date'))->format('Y-m'))
                ->filter(function (Builder $query, string $value) {
                    $query->whereDateFormat('date', $value, 'Y-m');
                }),
        ];
    }

    public function actions(): array
    {
        return [
            Action::make("Export to PDF")->primary(),
        ];
    }
}
