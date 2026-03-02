<?php

namespace App\Validation;

use App\Enums\ModificationType;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Validator;

class ValidateAttendanceTime
{
    public ?User $user;
    public ?Attendance $attendance;
    private Carbon $checkin;
    private Carbon $checkout;
    protected Carbon $date;

    public function __construct(Carbon $checkin, Carbon $checkout, ?User $user = null, ?Attendance $attendance = null)
    {
        $this->user = $user ?: user();
        $this->attendance = $attendance;

        $this->checkin = $checkin;
        $this->checkout = $checkout;
    }

    public function __invoke(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()){
            return;
        }

        $fail = fn(string $message) => $validator->errors()->add(
            'attendance',
            __($message)
        );

        $checkin = $this->checkin;
        $checkout = $this->checkout;

        if ($checkin->gte($checkout) || $checkout->isFuture()) {
            $fail('Invalid checkout time');
            return;
        }

        // check in existing attendances
        $attendances = $this->user
            ->attendances()
            ->whereDate('date', $checkin->toDateString())
            ->when($this->attendance, fn(Builder $query) => $query->except($this->attendance->id))
            ->get();

        foreach ($attendances as $attendance) {
            if ($this->attendanceAlreadyExists($attendance->checkin, $attendance->checkout)) {
                $fail('You already have an attendance in this time');
                return;
            }
        }

        // check in pending attendances
        foreach ($this->user->pendingAttendances as $modification) {
            $originalAttendance = $modification->modifiable;
            $modifiedAttendance = $modification->type == ModificationType::Create ? $modification->source : $modification->data;

            // we might have pending attendances where user have only modified either checkin or checkout
            // so we check if either is missing, we get it from attendance table directly

            $checkin = $modifiedAttendance->checkin || is_null($originalAttendance)
                ? Carbon::parse($modifiedAttendance->checkin)
                : $originalAttendance->checkin;

            $checkout = $modifiedAttendance->checkout || is_null($originalAttendance)
                ? Carbon::parse($modifiedAttendance->checkout)
                : $originalAttendance->checkout;

            if ($this->attendanceAlreadyExists($checkin, $checkout)) {
                $fail('You already have a pending attendance in this time');
                return;
            }
        }
    }


    /**
     * Checks whether the given check-in and check-out times
     * already exists and overlaps with this attendance.
     *
     * @param  Carbon  $attendance_checkin The check-in time of the attendance to check.
     * @param  Carbon  $attendance_checkout The check-out time of the attendance to check.
     *
     * @return bool True if an attendance with the given check-in and check-out times already exists and overlaps with this attendance, false otherwise.
     */
    private function attendanceAlreadyExists(Carbon $attendance_checkin, Carbon $attendance_checkout): bool
    {
        // Get the check-in and check-out times being entered.
        $checkin = $this->checkin;
        $checkout = $this->checkout;

        // Check if the entered attendance is the same as the existing attendance.
        if ($checkin->eq($attendance_checkin) && $checkout->eq($attendance_checkout)) {
            return true;
        }

        // Check if the entered attendance is completely contained within the existing attendance.
        if ($checkin->gte($attendance_checkin) && $checkout->lte($attendance_checkout)) {
            return true;
        }

        // Check if the existing attendance. is completely contained within the entered attendance.
        if ($checkin->lte($attendance_checkin) && $checkout->gte($attendance_checkout)) {
            return true;
        }

        // Check if the entered attendance overlaps with the beginning of the existing attendance.
        if ($checkin->lte($attendance_checkin) && $checkout->gt($attendance_checkin) && $checkout->lte($attendance_checkout)) {
            return true;
        }

        // Check if the entered attendance overlaps with the end of the existing attendance.
        if ($checkin->gte($attendance_checkin) && $checkout->gte($attendance_checkout) && $checkin->lt($attendance_checkout)) {
            return true;
        }

        // If none of the above conditions were met, the attendance does not overlap with the existing attendance.
        return false;
    }
}
