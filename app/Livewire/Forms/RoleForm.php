<?php

namespace App\Livewire\Forms;

use App\Livewire\Traits\LogsActivity;
use App\Models\Role;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use WireElements\Pro\Components\Modal\Modal;

class RoleForm extends Modal
{
    use LogsActivity;

    public Role|int $role;

    public string $heading;
    public bool $editing = false;

    protected function rules(): array
    {
        return [
            'role.display_name' => 'required',
            'role.name' => [
                "required",
                Rule::unique('roles', 'name')->ignore($this->role?->id)
            ],
        ];
    }

    public function mount(Role $role): void
    {
        $this->heading = __('Create Role');
        $this->role = $role;

        if ($this->role->id){
            $this->editing = true;
            $this->heading = __('Edit Role');
        }
    }

    public function render(): View
    {
        $data = array();

        return view('livewire.forms.role-form', $data);
    }

    public function submit(): void
    {
        $this->validate();

        $this->role->save();

        $this->close(andDispatch: [
            'refresh',
            'flashNotification' => ['message' => __('Role updated')]
        ]);
    }
}
