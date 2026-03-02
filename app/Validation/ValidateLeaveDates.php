<?php

namespace App\Validation;

use App\Helpers\LeavesHelper;
use App\Models\Leave;
use App\Models\LeaveReason;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\Validator;
use Throwable;

class ValidateLeaveDates
{
    private ?User $user;
    private ?Leave $leave;
    private string|Carbon $startsOn;
    private string|Carbon $endsOn;
    private ?int $reasonId;

    public function __construct(?User $user = null, ?Leave $leave = null, array $data = [])
    {
        $this->user = $user ?: user();
        $this->leave = $leave;
        $this->startsOn = $data['starts_on'];
        $this->endsOn = $data['ends_on'];
        $this->reasonId = $data['reason_id'];
    }

    /**
     * Run the validation rule.
     *
     * @param  Validator  $validator
     *
     * @return void
     * @throws Throwable
     */
    public function __invoke(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()){
            return;
        }

        $fail = fn(string $message) => $validator->errors()->add(
            'dates',
            __($message)
        );

        $starts_on = Carbon::parse($this->startsOn);
        $ends_on = Carbon::parse($this->endsOn);

        $reason = LeaveReason::find($this->reasonId);

        if (!$reason){
            $fail('Reason is required');
            return;
        }

        if ($reason->deductible && ($starts_on->year !== $ends_on->year)) {
            $fail('Vacation leaves should start and end in the same year');
            return;
        }

        try {
            $leaveRequired = LeavesHelper::getLeaveDates($starts_on, $ends_on, $this->user)->count();
        } catch (\Exception $exception) {
            $fail($exception->getMessage());
            return;
        }

        $leaveDatesChanged = true;

        // exclude already approved leave days from leave required
        if ($this->leave) {
            $leaveRequired -= $this->leave->days;

            // check if start date or end date has changed
            $leaveDatesChanged = $this->leave->starts_on != $starts_on || $this->leave->ends_on != $ends_on;
        }

        //leave required can be negative (e.g in case leave has been decreased)
        if ($leaveRequired == 0 && $leaveDatesChanged) {
            $fail('You cannot request leave in these dates');
            return;
        }

        $leaveAlreadyExist = $this->user->leaves()
            ->when($this->leave?->id,
                fn($query) => $query->except($this->leave->id)
            )
            ->overLapping($starts_on, $ends_on)
            ->count();

        if ($leaveAlreadyExist) {
            $fail('You already have a leave in these dates');
            return;
        }

        if (user()->can('ignore leaves balance')) {
            return;
        }

        if ( ! $reason->deductible) {
            return;
        }

        $leaveBalance = $this->user->calculateLeaveBalance($starts_on);


        if ($leaveRequired > $leaveBalance) {
            $fail('You do not have enough leaves for this category');
        }
    }


}
