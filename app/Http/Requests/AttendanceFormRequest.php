<?php

namespace App\Http\Requests;

use App\Models\Attendance;
use App\Models\User;
use App\Validation\ValidateAttendanceTime;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttendanceFormRequest extends FormRequest
{
    private ?Attendance $attendance = null;
    private bool $isUpdateRequest = false;

    protected function prepareForValidation(): void
    {
        if ($this->isMethod('put')) {
            $this->prepareForUpdateValidation();
        } else {
            $this->prepareForCreateValidation();
        }
    }

    private function prepareForCreateValidation(): void
    {
        $user_id = $this->user_id;

        // if user can not create attendance for all, and user id is not set,
        // we set user_id to currently authenticated user id
        if (! auth()->user()->can('create manual attendance for all')) {
            $user_id = $this->attendance->user_id ?? auth()->id();
        }

        $this->merge([
            'user_id' => $user_id,
            'created_by' => $user_id == auth()->id() ? null : auth()->id()
        ]);
    }

    private function prepareForUpdateValidation(): void
    {
        $this->isUpdateRequest = true;
        $this->attendance = $this->route('attendance');

        $this->merge([
            'date' => $this->attendance->date->toDateString(),
            'user_id' => $this->attendance->user_id,
            'updated_by' => $this->attendance->user_id == auth()->id() ? null: auth()->id()
        ]);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'created_by' => ['nullable', 'exists:users,id'],
            'checkin' => 'required',
            'checkout' => 'required',
            'date' => Rule::when(! $this->isUpdateRequest, fn() => ['required', 'date', 'before_or_equal:today']),
        ];
    }

    public function after(): array
    {
        return [
            new ValidateAttendanceTime(
                Carbon::create()->setDateFrom($this->date)->setTimeFrom($this->checkin),
                Carbon::create()->setDateFrom($this->date)->setTimeFrom($this->checkout),
                User::find($this->user_id),
                $this->attendance
            ),
        ];
    }

    public function authorize(): bool
    {
        // if user is creating/updating attendance for someone else, we need to check the permission!

        if ($this->isUpdateRequest && $this->updated_by) {
            return $this->user()->can('edit any attendance');
        }

        if ($this->created_by) {
            return $this->user()->can('create manual attendance for all');
        }

        return true;
    }

    public function passedValidation(): void
    {
        $this->merge([
            'checkin' => Carbon::create()->setDateFrom($this->date)->setTimeFrom($this->checkin),
            'checkout' => Carbon::create()->setDateFrom($this->date)->setTimeFrom($this->checkout),
        ]);
    }
}
