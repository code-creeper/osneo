<?php

namespace App\Livewire\Forms;

use App\Livewire\Traits\LogsActivity;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use WireElements\Pro\Components\Modal\Modal;

class UserForm extends Modal
{
    use LogsActivity;

    public User|int $user;

    public string $heading;
    public bool $editing = false;

    public $secondaryRoleIds = [];

    public $dob;
    public $activateOn;
    public $deActivateOn;

    public function mount(?int $user = null): void
    {
        $this->heading = __('Create User');

        $this->user = $user ? User::forceFind($user) : new User();

        if ($this->user->id){
            $this->editing = true;
            $this->heading = __('Edit User');

            $this->dob = $this->user->dob?->date();
            $this->activateOn = $this->user->activate_on?->date();
            $this->deActivateOn = $this->user->deactivate_on?->date();

            $this->secondaryRoleIds = $this->user->roles
                ->where('id', '!=', $this->user->role_id)
                ->pluck('id')->toArray();
        }
    }

    protected function rules(): array
    {
        return [
            'user.first_name' => 'required',
            'user.last_name' => 'required',
            'user.email' => [
                'email',
                Rule::unique('users', 'email')->ignore($this->user->id)
            ],
            'user.active' => 'required|in:0,1',
            'user.role_id' => 'required',
            'user.gender' => 'required',

            'user.activate_on' => 'nullable',
            'user.deactivate_on' => 'nullable',
            'user.ssn' => 'nullable',
            'user.birth_name' => 'nullable',
            'user.birthplace' => 'nullable',
            'user.dob' => 'nullable',
            'user.address' => 'nullable',

            'secondaryRoleIds' => 'nullable|array',
        ];
    }

    public function render(): View
    {
        $data = array();

        $roles = Role::all();

        $data['genders'] = array(
            '' => 'Select',
            'male' => 'Male',
            'female' => 'Female',
        );

        $data['statuses'] = array(
            '' => 'Select',
            '1' => 'Active',
            '0' => 'Inactive',
        );

        $data['primaryRoles'] = $roles->where('primary', 1)->toKeyValuePair(value: 'display_name');
        $data['secondaryRoles'] = $roles->where('primary', 0)->toKeyValuePair(value: 'display_name');

        return view('livewire.forms.user-form', $data);
    }

    public function submit(): void
    {
        $this->validate();

        $this->user->dob = $this->dob ? Carbon::parse($this->dob) : null;
        $this->user->activate_on = $this->activateOn ? Carbon::parse($this->activateOn) : null;
        $this->user->deactivate_on = $this->deActivateOn ? Carbon::parse($this->deActivateOn) : null;

        $roles = array_map('intval', array_merge([$this->user->role_id], $this->secondaryRoleIds));

        $this->user->save();

        $this->user->syncRoles($roles);

        $this->close(andDispatch: [
            'refresh',
            'flashNotification' => ['message' => __('User saved')]
        ]);
    }

    public static function attributes(): array
    {
        return [
            'size' => '7xl'
        ];
    }
}
