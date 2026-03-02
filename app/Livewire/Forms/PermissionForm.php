<?php

namespace App\Livewire\Forms;

use App\Livewire\Traits\LogsActivity;
use App\Models\Role;
use App\Traits\HasWireElementsPlaceholder;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Lazy;
use Spatie\Permission\Models\Permission;
use WireElements\Pro\Components\SlideOver\SlideOver;

class PermissionForm extends SlideOver
{
    use LogsActivity;

    use HasWireElementsPlaceholder;

    public Role|int $role;

    public int $selectedRoleId;
    public array $permissions = [];

    public string $heading;

    public function boot(): void
    {
        $this->authorize('manage permissions');
    }

    public function mount(Role $role): void
    {
        $this->selectedRoleId = $role->id;
        $this->handleSelectedRole();
    }

    public function handleSelectedRole(): void
    {
        $this->role = Role::find($this->selectedRoleId);

        $permissions = $this->role->permissions->pluck('id')->toArray();
        $this->permissions = array_map('strval', $permissions);

        $this->heading = __('Manage Permissions for ":role"', ['role' => $this->role->display_name]);
    }

    public function render(): View
    {
        $data = array();

        $data['roles'] = Role::all()->toKeyValuePair(value: 'display_name');

        $permissionGroups = Permission::groupBy('group')->where('group', '!=', 'general')->pluck('group');
        $permissionGroups[] = 'general';
        $data['permissionGroups'] = $permissionGroups;

        return view('livewire.forms.permission-form', $data);
    }

    public function submit(): void
    {
        $this->role->syncPermissions($this->permissions);

        $this->dispatch('flashNotification', message: __('Permissions updated'));
    }

    public static function attributes(): array
    {
        return [
            'size' => '6xl'
        ];
    }
}
