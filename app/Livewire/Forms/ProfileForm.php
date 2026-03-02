<?php

namespace App\Livewire\Forms;

use App\Livewire\Traits\LogsActivity;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use WireElements\Pro\Components\Modal\Modal;

class ProfileForm extends Modal
{
    use LogsActivity;

    public User|int $user;

    public string $heading;
    public bool $editing = false;

    public string $dob;

    public function mount(): void
    {
        $this->heading = __('Update Profile');
        $this->user = auth()->user();

        $this->dob = $this->user->dob?->date();
    }

    protected function rules(): array
    {
        return [
            'user.first_name' => 'required',
            'user.last_name' => 'required',
            'user.gender' => 'required',

            'user.ssn' => 'nullable',
            'user.birth_name' => 'nullable',
            'user.birthplace' => 'nullable',
            'user.dob' => 'nullable',
            'user.address' => 'nullable',
        ];
    }

    public function render(): View
    {
        $data = array();

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

        return view('livewire.forms.profile-form', $data);
    }

    public function submit(): void
    {
        $this->validate();

        $this->user->dob = $this->dob ? Carbon::parse($this->dob) : null;

        $this->user->save();

        $this->close(andDispatch: [
            'refresh',
            'flashNotification' => ['message' => __('Profile updated successfully')]
        ]);
    }

    public static function attributes(): array
    {
        return [
            'size' => '7xl'
        ];
    }
}
