<?php

namespace App\Livewire\Forms;

use App\Livewire\Traits\LogsActivity;
use App\Models\Attendance;
use App\Models\User;
use App\Validation\ValidateAttendanceTime;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use WireElements\Pro\Components\Modal\Modal;

class AttendanceForm extends Modal
{
    use LogsActivity;

    public Attendance|int $attendance;

    public bool $editing = false;
    public string $title;

    public ?int $userId = null;
    public string|array $hiddenFields = [];

    public string $date;
    public string $checkin;
    public string $checkout;

    public $comments;

    public function boot(): void
    {
        $this->withValidator(function (Validator $validator) {
            $validator->after([
                new ValidateAttendanceTime(
                    $this->attendance->checkin,
                    $this->attendance->checkout,
                    User::find($this->attendance->user_id),
                    $this->attendance
                ),
            ]);
        });
    }

    public function mount(Attendance $attendance): void
    {
        if ( ! is_array($this->hiddenFields)) {
            $this->hiddenFields = array($this->hiddenFields);
        }

        $this->title = __('Add New Attendance');
        $this->attendance = $attendance;

        if ($this->attendance->id) {
            $this->editing = true;
            $this->title = __('Edit Attendance');

            $this->date = $attendance->date->toDateString();
            $this->checkin = $attendance->checkin->format('H:i');
            $this->checkout = $attendance->checkout->format('H:i');
        } else {
            $this->attendance->user_id = null;
        }

        if ( ! $this->editing && auth()->user()->cannot('create manual attendance for all')) {
            $this->attendance->user_id = auth()->id();
            $this->hiddenFields[] = 'user_id';
        }

        if ($this->userId) {
            $this->attendance->user_id = $this->userId;
        }
    }

    public function render(): View
    {
        $data = array();
        $data['users'] = User::relevant()->get()->toKeyValuePair();

        return view('livewire.forms.attendance-form', $data);
    }

    public function rules(): array
    {
        return [
            'attendance.user_id' => ['required', 'exists:users,id'],
            'attendance.created_by' => ['nullable', 'exists:users,id'],
            'attendance.updated_by' => ['nullable', 'exists:users,id'],
            'checkin' => 'required',
            'checkout' => 'required',
            'date' => Rule::when(! $this->editing, fn() => ['required', 'date', 'before_or_equal:today']),
        ];
    }

    public function submit(): void
    {
        $this->attendance->checkin = Carbon::create()->setDateFrom($this->date)->setTimeFrom($this->checkin);
        $this->attendance->checkout = Carbon::create()->setDateFrom($this->date)->setTimeFrom($this->checkout);

        if ($this->editing) {
            $this->authorize('update', $this->attendance);
            $this->update();
        } else {
            $this->authorize('storeManually', $this->attendance);
            $this->store();
        }
    }

    private function store(): void
    {
        $this->validate();

        $this->attendance->created_by = $this->attendance->user_id == auth()->id() ? null : auth()->id();
        $this->attendance->date = $this->date;

        $user = User::find($this->attendance->user_id);
        $creatingForSelf = $this->attendance->created_by == null;

        if ( ! $creatingForSelf) {
            $this->authorize('create manual attendance for all');
        }

        if ( $creatingForSelf && $user->cannot('create manual attendance without approval')) {
            Attendance::requestCreation($this->attendance->only(['checkin', 'checkout', 'date']));

            $this->close(andDispatch: [
                'flashNotification' => ['message' => __('Request for creating attendance has been sent to admin')],
            ]);

            return;
        }

        $this->attendance->save();

        $this->dispatch('flashNotification', message: __('Attendance created'));
        $this->close();
    }

    private function update(): void
    {
        $this->validate();

        $this->attendance->updated_by = $this->attendance->user_id == auth()->id() ? null : auth()->id();

        $updatedBySelf = $this->attendance->updated_by == null;

        if ( ! $updatedBySelf) {
            $this->authorize('edit any attendance');
        }

        if ($updatedBySelf && $this->attendance->user->cannot('edit attendance without approval')) {
            $this->sendModificationRequest();
            return;
        }

        $this->attendance->save();

        $this->close(andDispatch: [
            'flashNotification' => ['message' => __('Attendance updated')],
        ]);
    }

    private function sendModificationRequest(): void
    {
        $data = $this->attendance->getDirty();
        $data['comments'] = $this->comments;

        if ($this->attendance->createModification($data)){
            $this->close(andDispatch: [
                'flashNotification' => ['message' => __('Request for modification has been sent to admin')],
                'refresh',
            ]);

            return;
        }

        $this->dispatch('flashNotification', message: __('No changes were made'), type: 'info');
    }
}
