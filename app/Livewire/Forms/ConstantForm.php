<?php

namespace App\Livewire\Forms;

use App\Livewire\Traits\LogsActivity;
use App\Models\Constant;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use WireElements\Pro\Components\Modal\Modal;

class ConstantForm extends Modal
{
    use LogsActivity;

    public Constant|int $constant;

    public string $heading;
    public bool $editing = false;

    protected function rules(): array
    {
        return [
            'constant.group' => 'required',
            'constant.key' => 'required|unique:constants,key'. ($this->constant?->id ? ",{$this->constant->id}" : ''),
            'constant.value' => 'required',
        ];
    }

    public function mount(Constant $constant): void
    {
        $this->heading = __('Create Constant');
        $this->constant = $constant;

        if ($this->constant->id) {
            $this->editing = true;
            $this->heading = __('Edit Constant');
        }
    }

    public function render(): View
    {
        $data = array();

        return view('livewire.forms.constant-form', $data);
    }

    public function submit(): void
    {
        $this->validate();

        $this->constant->save();

        $this->close(andDispatch: [
            'flashNotification' => ['message' => __('Constant saved')],
        ]);
    }

    public static function attributes(): array
    {
        return [
            'size' => '7xl',
        ];
    }
}
