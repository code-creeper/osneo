<?php

namespace App\Policies;

use App\Models\Leave;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeavePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->can('view all leaves')) {
            return true;
        }

        if ($user->can('view own leaves')) {
            return true;
        }
    }

    public function view(User $user, Leave $leave)
    {
        //
    }

    public function create(User $user)
    {
        if ($user->can('create leaves for all')) {
            return true;
        }

        if ($user->can('create leaves')) {
            return true;
        }
    }

    public function update(User $user, Leave $leave)
    {
        if ($leave->isApproved() || $leave->isRejected()){
            //return false
            //todo::temp allowed, handle permission correctly
            return $user->can('tag leaves');
        }

        if ($leave->pendingModification){
            return false;
        }

        if ($user->can('edit any leaves')) {
            return true;
        }

        if ($user->can('edit own leaves')) {
            return $leave->user_id == $user->id;
        }
    }

    public function delete(User $user, Leave $leave)
    {
        if ($leave->trashed()) {
            return false;
        }

        if ($leave->pendingModification){
            return false;
        }

        if ($user->can('delete any leaves')) {
            return true;
        }

        if ($user->can('delete own leaves')) {
            return $leave->user_id == $user->id;
        }
    }

    public function restore(User $user, Leave $leave)
    {
        //
    }

    public function forceDelete(User $user, Leave $leave)
    {
        //
    }

    public function approve(User $user, Leave $leave)
    {
        if (! $leave->isPending()){
            return false;
        }

        if ($user->can('approve leaves')) {
            return true;
        }
    }

    public function reject(User $user, Leave $leave)
    {
        if (! $leave->isPending()){
            return false;
        }

        if ($user->can('reject leaves')) {
            return true;
        }
    }

    public function calendar(User $user)
    {
        if ($user->can('view all leaves')) {
            return true;
        }
    }

    public function jsonOwn(User $user)
    {
        if ($user->can('view all leaves')
            || $user->can('view own leaves')
        ) {
            return true;
        }
    }

    public function jsonAll(User $user)
    {
        if ($user->can('view all leaves')) {
            return true;
        }
    }

    public function processClaim(User $user, Leave $leave)
    {
        if ($leave->reason_id != config('app.sick_leave_reason_id')){
            return false;
        }

        if ($leave->claim && $leave->claim->isProcessed()){
            return false;
        }

        if ($user->can('process insurance claims')) {
            return true;
        }
    }

    public function preApprove(User $user, Leave $leave)
    {
        if ($user->can('create pre-approved leaves for all')){
            return true;
        }

        if ($user->can('create pre-approved leaves')){
            return $user->id == $leave->user_id;
        }
    }
}
