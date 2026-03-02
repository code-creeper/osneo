<?php

namespace App\Livewire\Forms;

use App\Livewire\Traits\LogsActivity;
use App\Models\Announcement;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use WireElements\Pro\Components\Modal\Modal;

class AnnouncementForm extends Modal
{
    use LogsActivity;

    public Announcement|int $announcement;

    public string $title;
    public bool $editing = false;

    public array $userIds = [];
    public array $roleIds = [];


    protected function rules(): array
    {
        return [
            'announcement.subject' => 'required',
            'announcement.body' => 'required',
            'announcement.audience' => 'required',
            'userIds' => 'required_if:announcement.audience,user',
            'roleIds' => 'required_if:announcement.audience,role',
        ];
    }

    public function mount(Announcement $announcement): void
    {
        $this->title = __('Add New Announcement');

        $this->announcement = $announcement;

        if ($this->announcement->id){
            $this->editing = true;
            $this->title = __('Edit Announcement');

            $this->userIds = $this->announcement->users->pluck('id')->toArray();
            $this->roleIds = $this->announcement->role_ids ?? [];
        } else {
            $this->announcement->audience = 'all';
        }
    }

    public function render(): View
    {
        $data = array();

        $data['users'] = User::relevant()->oldest('first_name')->get()->toKeyValuePair();
        $data['roles'] = Role::oldest('display_name')->get()->toKeyValuePair(value: 'display_name');

        return view('livewire.forms.announcement-form', $data);
    }

    public function submit(): void
    {
        $this->validate();

        $this->announcement->role_ids = $this->roleIds;
        $this->announcement->save();

        $users = [];

        switch ($this->announcement->audience){
            case 'all':
                $users = User::relevant()->get();
                break;
            case 'user':
                $users = User::find($this->userIds);
                break;
            case 'role':
                $users = User::relevant()->role($this->roleIds)->get();
                break;
        }

        $this->announcement->users()->sync($users);

        $this->close(andDispatch: [
            'refresh',
            'flashNotification' => ['message' => __('Announcement saved')]
        ]);
    }

    public static function attributes(): array
    {
        return [
            'size' => '3xl'
        ];
    }
}
