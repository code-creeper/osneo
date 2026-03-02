<?php

namespace App\Livewire\Datatables;

use App\LaravelLivewireTables\Traits\WithCustomizations;
use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Action;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\MenuItem;
use WireElements\Pro\Concerns\InteractsWithConfirmationModal;

class RoleDatatable extends DataTableComponent
{
    use InteractsWithConfirmationModal;
    use WithCustomizations;

    protected $listeners = ['refresh' => '$refresh'];

    public function boot(): void
    {
        $this->authorize('viewAny', Role::class);
    }

    public function configure(): void
    {
        $this->useDefaults();
        $this->setTitle("Roles Management");

        $this->setSearchDisabled();
        $this->setColumnSelectDisabled();
        $this->setPaginationDisabled();
    }

    public function builder(): Builder
    {
        return Role::query()->select('roles.*');
    }

    public function columns(): array
    {
        return [
            Column::make("Role", 'name'),
            Column::make("Name", 'display_name'),

            MenuColumn::make('Actions', 'actions')
                ->actions([
                    MenuItem::make('')
                        ->title(fn($row) => 'Manage Permissions')
                        ->icon('fal fa-list me-1')
                        ->permission(fn($row) => user()->can('update', $row))
                        ->wireElement(fn($row) => [
                            'type' => 'slide-over',
                            'component' => 'forms.permission-form',
                            'params' => ['role' => $row->id]
                        ]),

                    MenuItem::make('')
                        ->title(fn($row) => 'Preferences')
                        ->icon('fal fa-cog me-1')
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
                        ->title(fn($row) => 'Edit Role')
                        ->icon('fal fa-edit me-1')
                        ->permission(fn($row) => user()->can('update', $row))
                        ->wireElement(fn($row) => [
                            'type' => 'modal',
                            'component' => 'forms.role-form',
                            'params' => ['role' => $row->id]
                        ]),

                    MenuItem::make('')
                        ->title(fn($row) => 'Duplicate')
                        ->icon('fal fa-copy me-1')
                        ->permission(fn($row) => user()->can('create', $row))
                        ->attributes(fn($row) => [
                            'wire:click' => "duplicate($row->id)"
                        ]),

                    MenuItem::make('')
                        ->title(fn($row) => 'Delete')
                        ->icon('fa fa-trash-alt me-1')
                        ->permission(fn($row) => user()->can('delete', $row))
                        ->attributes(fn($row) => [
                            'class' => 'text-danger',
                            'wire:click' => "delete($row->id)"
                        ])
                ]),
        ];
    }

    public function actions(): array
    {
        return [
            Action::make("Add New")
                ->primary(outline: true)
                ->icon('fal fa-plus')
                ->hideIf(user()->cannot('create', Role::class))
                ->wireModal('forms.role-form'),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Component Actions
    |--------------------------------------------------------------------------
    */

    public function delete(Role $role): void
    {
        $this->authorize('delete', $role);

        $this->askForConfirmation(function () use ($role){
            $role->permissions()->detach();
            $role->delete();
            $this->dispatch('flashNotification', message:__( "Role deleted"));
        });
    }

    public function duplicate(Role $role): void
    {
        $newRole = $role->replicate();
        $newRole->name .= "_copy";
        $newRole->display_name .= "_copy";

        $newRole->save();
        $newRole->permissions()->sync($role->permissions->pluck('id')->toArray());

        $this->dispatch('flashNotification', message:__("Role replicated Successfully"));
    }
}
