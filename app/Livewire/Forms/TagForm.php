<?php

namespace App\Livewire\Forms;

use App\Livewire\Traits\LogsActivity;
use App\Models\Tag;
use Illuminate\Contracts\View\View;
use WireElements\Pro\Components\Modal\Modal;

class TagForm extends Modal
{
    use LogsActivity;

    public Tag|int $tag;

    public string $heading;
    public bool $editing = false;

    protected function rules(): array
    {
        return [
            'tag.name' => 'required',
            'tag.model' => 'required',
            'tag.color' => 'nullable',
        ];
    }

    public function mount(Tag $tag): void
    {
        $this->heading = __('Create Tag');
        $this->tag = $tag;

        if ($this->tag->id){
            $this->editing = true;
            $this->heading = __('Edit Tag');
        }
    }

    public function render(): View
    {
        $data = array();
        $data['modules'] = config('constants.taggable_models');

        return view('livewire.forms.tag-form', $data);
    }

    public function submit(): void
    {
        $this->validate();

        $this->tag->save();

        $this->close(andDispatch: [
            'refresh',
            'flashNotification' => ['message' => __('Tag saved')]
        ]);
    }
}
