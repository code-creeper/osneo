<?php

namespace App\Policies;

use App\Models\LeaveTransaction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeaveTransactionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->can('view all leave transactions')
            || $user->can('view own leave transactions')
        ) {
            return true;
        }
    }

    public function view(User $user, LeaveTransaction $leaveTransaction)
    {
        //
    }

    public function create(User $user)
    {
        if ($user->can('create leave transactions')) {
            return true;
        }
    }

    public function update(User $user, LeaveTransaction $leaveTransaction)
    {
        if ($user->can('edit leave transactions')) {
            return true;
        }
    }

    public function delete(User $user, LeaveTransaction $leaveTransaction)
    {
        if ($user->can('delete leave transactions')) {
            return true;
        }
    }

    public function restore(User $user, LeaveTransaction $leaveTransaction)
    {
        //
    }

    public function forceDelete(User $user, LeaveTransaction $leaveTransaction)
    {
        //
    }
}
