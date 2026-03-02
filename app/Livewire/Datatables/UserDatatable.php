<?php

namespace App\Livewire\Datatables;

use App\LaravelLivewireTables\Traits\WithCustomizations;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Action;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuItem;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;
use WireElements\Pro\Concerns\InteractsWithConfirmationModal;

class UserDatatable extends DataTableComponent
{
    use WithCustomizations;
    use InteractsWithConfirmationModal;

    protected $listeners = ['refresh' => '$refresh'];

    public function boot(): void
    {
        $this->authorize('viewAny', User::class);
    }

    public function configure(): void
    {
        $this->useDefaults();

        $this->defaultSortColumn = 'first_name';
        $this->defaultSortDirection = 'asc';

        $this->setTitle("Users");

        $this->setFilterSlideDownDefaultStatusDisabled();
    }

    public function builder(): Builder
    {
        return User::query()
            ->withoutGlobalScope('active')
            ->relevant()
            ->with([
                'roles' => fn($builder) => $builder->wherePrimary(0),
                'primaryRole'
            ])
            ->select('users.*');
    }

    public function columns(): array
    {
        return [
            Column::make("User")
                ->from(fn($row) => "$row->first_name  $row->last_name")
                ->sortable()
                ->searchable(fn($builder) => $builder->whereAnyColumnLike($this->search, [
                    'first_name', 'last_name'
                ])),

            Column::make("Email", "email")->sortable()->searchable(),

            Column::make("Primary Role")
                ->from(fn($row) => "<span class='badge badge-outline-success py-1 badge-normal me-1'>{$row->primaryRole?->display_name}</span>")
                ->html(),

            Column::make("Secondary Roles")->from(function ($row){
                $value = "";

                foreach ($row->roles as $role){
                    $value .= "<span class='badge badge-outline-info py-1 badge-normal me-1'>$role->display_name</span>";
                }
                return $value;
            })->html(),

            Column::make("Date of Birth", "dob")
                ->format(fn($value, $row) => $row->dob?->date())
                ->deselected()
                ->sortable(),

            Column::make("Employment Period")
                ->from(function ($row) {
                    $value = null;

                    foreach ($row->employments as $employment) {
                        $value .= "<span class='badge badge-primary-lighten p-1 mb-1'>$employment->period</span><br>";
                    }

                    return $value ?? "<span class='badge badge-warning-lighten p-1 mb-1'>No Employment Found</span>";
                })->html(),

            Column::make("Status", "active")->format(fn($value, $row) => $row->active ? 'Active' : 'Inactive')->sortable(),

            MenuColumn::make('Actions')
                ->actions([
                    MenuItem::make('')
                        ->title(fn($row) => 'Login As')
                        ->form()
                        ->icon('fal fa-sign-in mr-1')
                        ->visible(fn($row) => $row->active)
                        ->permission(fn() => user()->can('switch to other users'))
                        ->attributes(fn($row) => [
                            'wire:click' => "loginAs($row->id)"
                        ]),

                    MenuItem::make('')
                        ->title(fn($row) => 'Adjust Leaves')
                        ->visible(fn($row) => $row->active)
                        ->icon('fal fa-comment-edit mr-1')
                        ->permission(fn($row) => user()->can('create leave transactions', $row))
                        ->wireElement(fn($row) => [
                            'type' => 'modal',
                            'component' => 'forms.leave-transaction-form',
                            'params' => ['user' => $row->id]
                        ]),

                    MenuItem::make('')
                        ->title(fn($row) => 'Preferences')
                        ->icon('fal fa-cog me-1')
                        ->visible(fn($row) => $row->active)
                        ->permission(fn($row) => user()->can('create', $row))
                        ->wireElement(fn($row) => [
                            'type' => 'modal',
                            'component' => 'forms.preference-form',
                            'params' => [
                                'model' => $row->id,
                                'type' => class_basename($row)
                            ]
                        ]),

                    MenuItem::make('')
                        ->title(fn($row) => 'Employments')
                        ->icon('fal fa-briefcase me-1')
                        ->visible(fn($row) => $row->active)
                        ->permission(fn($row) => user()->can('create', $row))
                        ->wireElement(fn($row) => [
                            'type' => 'modal',
                            'component' => 'modals.employment-management',
                            'params' => ['user' => $row->id,]
                        ]),

                    MenuItem::make('')
                        ->title(fn($row) => 'Edit')
                        ->icon('fal fa-pen me-1')
                        ->permission(fn($row) => user()->can('update', $row))
                        ->wireElement(fn($row) => [
                            'type' => 'modal',
                            'component' => 'forms.user-form',
                            'params' => ['user' => $row->id]
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
        return [
            SelectFilter::make('Status')
                ->setFilterDefaultValue(1)
                ->setFirstOption()
                ->filter(fn(Builder $builder, $value) => $builder->where('active', $value))
                ->options([
                    '1' => 'Active',
                    '0' => 'Inactive',
                ]),
        ];
    }

    public function actions(): array
    {
        return [
            Action::make("Update Summaries")
                ->primary()
                ->hideIf(user()->cannot('access admin area'))
                ->wireModal('modals.update-attendance-summaries'),

            Action::make("Add New")
                ->primary(outline: true)
                ->icon('fal fa-plus')
                ->hideIf(user()->cannot('create', User::class))
                ->wireModal('forms.user-form'),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Component Actions
    |--------------------------------------------------------------------------
    */

    public function delete($userId): void
    {
        $user = User::forceFind($userId);

        $this->authorize('delete', $user);

        $this->askForConfirmation(function () use ($user){
            $user->delete();
            $this->dispatch('flashNotification', message: __("User deleted"));
        });
    }

    public function loginAs(User $user): void
    {
        $user->loginAs();

        $this->dispatch(
            'flashNotification',
            message: __('You are now logged in as :user', ['user' => $user->name])
        );

        $this->dispatch('refreshPage');
    }
}
