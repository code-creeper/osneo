<?php

namespace App\Livewire\Forms;

use App\Livewire\Traits\LogsActivity;
use App\Models\ManualEntry;
use App\Models\User;
use App\Notifications\ManualEntryNotification;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use WireElements\Pro\Components\Modal\Modal;

class ManualEntryForm extends Modal
{
    use LogsActivity;

    public ManualEntry|int $entry;

    public string $title;
    public bool $editing = false;

    public ?string $date = null;
    public ?string $duration = null;

    protected function rules(): array
    {
        return [
            'entry.user_id' => 'required|exists:users,id',
            'entry.date' => 'required',
            'entry.duration' => 'required|not_in:0|numeric',
            'entry.comments' => 'nullable',
            'entry.payout' => 'nullable',
        ];
    }

    public function mount(ManualEntry $entry): void
    {
        $this->title = __('Create Manual Entry');

        $this->entry = $entry;

        if ($this->entry->id){
            $this->editing = true;
            $this->title = __('Edit Manual Entry');

            $this->date = $entry->date->date();
            $this->duration = minutesToDurationInput($entry->duration);
        }
    }

    public function render(): View
    {
        $data = array();
        $data['users'] = User::relevant()->get()->toKeyValuePair();

        return view('livewire.forms.manual-entry-form', $data);
    }

    public function submit(): void
    {
        $this->entry->duration = durationInputToMinutes($this->duration, false);
        $this->entry->date = $this->date ? Carbon::parse($this->date) : null;

        $this->validate();

        $this->entry->save();

        if ($this->entry->user_id !== auth()->id()){
            $this->entry->user->notify(new ManualEntryNotification($this->entry));
        }

        $this->close(andDispatch: [
            'refresh',
            'flashNotification' => ['message' => __('Manual entry saved')]
        ]);
    }

    public static function attributes(): array
    {
        return [
            'size' => '5xl'
        ];
    }
}
