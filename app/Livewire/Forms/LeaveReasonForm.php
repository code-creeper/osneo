<?php

namespace App\Livewire\Forms;

use App\Helpers\GeneralHelper;
use App\Livewire\Traits\LogsActivity;
use App\Models\LeaveReason;
use App\Rules\RequiredForCurrentLocaleRule;
use Illuminate\Contracts\View\View;
use WireElements\Pro\Components\Modal\Modal;

class LeaveReasonForm extends Modal
{
    use LogsActivity;

    public LeaveReason|int $leaveReason;

    public string $title;
    public bool $editing = false;

    public array $name;

    protected function rules(): array
    {
        return array_merge(
            GeneralHelper::translateableFieldRules('name', [
                new RequiredForCurrentLocaleRule(), 'string', 'max:255',
            ]), [
            'leaveReason.color' => 'nullable',
            'leaveReason.paid' => 'nullable|bool',
            'leaveReason.deductible' => 'nullable|bool',
        ]);
    }

    public function mount(LeaveReason $leaveReason): void
    {
        $this->title = __('Create Leave Reason');
        $this->leaveReason = $leaveReason;

        $this->name = $leaveReason->getTranslations('name');

        if ($this->leaveReason->id) {
            $this->editing = true;
            $this->title = __('Edit Leave Reason');
        } else {
            $this->leaveReason->paid = false;
            $this->leaveReason->deductible = false;
        }
    }

    public function render(): View
    {
        $data = array();

        return view('livewire.forms.leave-reason-form', $data);
    }

    public function submit(): void
    {
        $this->validate();

        $this->leaveReason->name = $this->name;
        $this->leaveReason->save();

        $this->close(andDispatch: [
            'refresh',
            'flashNotification' => ['message' => __('Leave reason updated')],
        ]);
    }
}
