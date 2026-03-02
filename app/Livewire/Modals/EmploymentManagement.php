<?php

namespace App\Livewire\Modals;

use App\Livewire\Traits\LogsActivity;
use App\Models\Employment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use WireElements\Pro\Components\Modal\Modal;
use WireElements\Pro\Concerns\InteractsWithConfirmationModal;

class EmploymentManagement extends Modal
{
    use LogsActivity;

    use InteractsWithConfirmationModal;

    public int|User $user;
    public Employment $selectedEmployment;
    public bool $creatingNewEmployment = false;
    public Collection $employments;
    public ?string $started_on = null;
    public ?string $ended_on = null;

    public bool $headless = false;

    public array $off_days = [];

    protected array $rules = [
        'selectedEmployment.user_id' => 'required',
        'selectedEmployment.weekly_target_time' => 'required',
        'selectedEmployment.monthly_target_time' => 'required_if:selectedEmployment.employment_type,hourly',
        'selectedEmployment.employment_type' => 'required',
        'selectedEmployment.hourly_rate' => 'required|numeric',
        'started_on' => 'required',
        'ended_on' => 'nullable',
        'off_days' => 'nullable|array',
    ];

    public function mount(User $user): void
    {
        $this->user = $user;
        $this->selectEmployment($user->employment);
    }

    public function render(): View
    {
        $data = array();

        $data['title'] = __('Employments');

        $this->employments = $this->user->employments()->oldest('started_on')->get();
        $data['employment_types'] = config('constants.employment_types');

        return view('livewire.modals.employment-management', $data);
    }

    public function submit(): void
    {
        $this->validate();

        $this->selectedEmployment->off_days = $this->off_days;
        $this->selectedEmployment->started_on = Carbon::parse($this->started_on);
        $this->selectedEmployment->ended_on = $this->ended_on ? Carbon::parse($this->ended_on) : null;
        $this->selectedEmployment->save();

        $this->user->refresh();

        $this->selectEmployment($this->selectedEmployment);
        $this->dispatch('flashNotification', message: __('Employment updated'));
    }

    public function delete(): void
    {
        $this->askForConfirmation(
            callback: function () {
                $this->selectedEmployment->delete();
                $this->user->refresh();
                $this->selectEmployment($this->user->employment);
                $this->dispatch('flashNotification', message: __('Employment deleted'));
            },
        );
    }

    public function selectEmployment(?Employment $employment): void
    {
        if (is_null($employment) || !$employment?->id){
            $this->openNewForm();
            return;
        }

        $this->started_on = $employment->started_on->date();
        $this->ended_on = $employment->ended_on?->date();
        $this->off_days = $employment->off_days;

        //$this->resetValidation();
        $this->creatingNewEmployment = false;
        $this->selectedEmployment = $employment;
    }

    public function openNewForm(): void
    {
        $this->started_on = now()->date();
        $this->ended_on = null;

        $this->off_days = [];
        $this->creatingNewEmployment = true;
        $this->selectedEmployment = new Employment();
        $this->selectedEmployment->user_id = $this->user->id;
        $this->selectedEmployment->employment_type = 'weekly';
        $this->selectedEmployment->off_days = [];
        $this->selectedEmployment->weekly_target_time = null;
        $this->selectedEmployment->monthly_target_time = null;
    }

    public static function attributes(): array
    {
        return [
            'size' => '7xl'
        ];
    }
}
