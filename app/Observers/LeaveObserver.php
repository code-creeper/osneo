<?php

namespace App\Observers;

use App\Enums\LeaveAction;
use App\Models\Leave;
use App\Notifications\LeaveActionTaken;
use DB;
use Exception;

class LeaveObserver
{
    /**
     * @throws Exception
     */
    public function created(Leave $leave): void
    {
        try {
            $leave->createLeaveDays();
        } catch (Exception $exception) {
            $leave->delete();
            throw $exception;
        }

        $leave->updateAttendanceSummary();

        if ($leave->isCreatedByAdmin()) {
            $leave->user->notify(new LeaveActionTaken($leave, LeaveAction::Created));
        }
    }

    /**
     * @throws Exception
     */
    public function updating(Leave $leave): void
    {
        DB::beginTransaction();
        try {
            $leave->leaveDays()->delete();
            $leave->createLeaveDays();
        } catch (\Exception $exception) {
            DB::rollBack();
            $leave->refresh();
            throw $exception;
        }
        DB::commit();
    }

    public function updated(Leave $leave): void
    {
        $leave->updateAttendanceSummary();
    }

    public function deleted(Leave $leave): void
    {
        $leave->leaveDays()->delete();
        $leave->updateAttendanceSummary();
    }
}
