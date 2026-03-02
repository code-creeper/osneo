<?php

namespace App\Livewire\Forms;

use App\Livewire\Traits\LogsActivity;
use App\Models\DocumentType;
use App\Models\Preference;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use WireElements\Pro\Components\Modal\Modal;

class PreferenceForm extends Modal
{
    use LogsActivity;

    public int|Role|User $model;

    public string $heading;
    public string $modelName;

    public array $preferences = [];

    public function mount(int|Role|User $model, $type = null): void
    {
        $this->model = $model;

        if (is_int($model) && $type == 'Role'){
            $this->model = Role::findOrFail($model);
        }

        if (is_int($model) && $type == 'User'){
            $this->model = User::findOrFail($model);
        }

        $this->modelName = class_basename($this->model);

        $this->heading = __('Manage :model Preferences', ['model' => $this->modelName]);

        foreach (array_keys(config('preferences')) as $preferenceName){
            $this->preferences[$preferenceName] = $this->model->getPreference($preferenceName);
        }

    }

    public function rules(): array
    {
        $rules = array();

        foreach (array_keys(config('preferences')) as $preferenceName){
            $rules["preferences.$preferenceName"] = config('preferences')[$preferenceName]['rules'] ?? 'nullable';
        }

        return $rules;
    }

    public function render(): View
    {
        $data = array();

        $data['documentTypes'] = DocumentType::all()->toKeyValuePair();

        return view('livewire.forms.preference-form', $data);
    }

    public function submit(): void
    {
        $foreignId = $this->modelName == 'User' ? 'user_id' : 'role_id';

        $this->validate();

        foreach (array_keys(config('preferences')) as $preferenceName){
            Preference::updateOrCreate([
                $foreignId => $this->model->id,
                'name' => $preferenceName,
            ], [
                'value' => $this->preferences[$preferenceName],
            ]);
        }

        $this->close(andDispatch: [
            'flashNotification' => ['message' => __('Preferences saved')]
        ]);
    }
}
