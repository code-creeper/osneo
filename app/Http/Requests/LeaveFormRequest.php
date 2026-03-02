<?php

namespace App\Http\Requests;

use App\Helpers\LeavesHelper;
use App\Models\Leave;
use App\Models\User;
use App\Validation\ValidateLeaveDates;
use Carbon\CarbonPeriod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class LeaveFormRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;
    private bool $isUpdateRequest = false;
    private ?Leave $leave = null;
    private CarbonPeriod $leaveDates;

    protected function prepareForValidation(): void
    {
        if ($this->isMethod('put')){
            $this->isUpdateRequest = true;
            $this->leave = $this->route('leave');
        }

        $user_id = $this->user_id;

        // if user can not create leaves for all, and user id is not set,
        // we set user_id to currently authenticated user id
        if (! auth()->user()->can('create leaves for all')) {
            $user_id = $this->leave->user_id ?? auth()->id();
        }

        $this->merge([
            'user_id' => $user_id,
            'created_by' => $user_id == auth()->id() ? null : auth()->id()
        ]);

        if ($this->isUpdateRequest){
            $this->request->remove('created_by');
        }
    }

    public function rules(): array
    {
        return [
            'user_id' => [$this->isUpdateRequest ? 'nullable' : 'required', 'exists:users,id'],
            'created_by' => ['nullable', 'exists:users,id'],
            'reason_id' => ['bail', 'required', 'integer', 'exists:leave_reasons,id'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date'],
            'days' => ['nullable'],
            'tags' => ['nullable'],
        ];
    }

    public function after(): array
    {
        return [
            new ValidateLeaveDates(
                User::find($this->user_id),
                $this->leave
            ),
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()){
                    return;
                }

                try {
                    $this->leaveDates = LeavesHelper::getLeaveDates(
                        $this->date('starts_on'),
                        $this->date('ends_on'),
                        User::find($this->isUpdateRequest ? $this->leave->user_id : $this->user_id)
                    );
                } catch (\Exception $exception){
                    $validator->errors()->add('starts_on', __($exception->getMessage()));
                }
            },
        ];
    }

    public function passedValidation(): void
    {
        $this->merge([
            'days' => $this->leaveDates->count(),
        ]);
    }

    public function authorize(): bool
    {
        if ($this->created_by) {
            return $this->user()->can('create leaves for all');
        }

        return true;
    }
}
