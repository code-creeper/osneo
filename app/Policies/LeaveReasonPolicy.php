<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeaveReasonPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->can('view reasons')) {
            return true;
        }
    }

    public function view(User $user)
    {
        //
    }

    public function create(User $user)
    {
        if ($user->can('create reasons')) {
            return true;
        }
    }

    public function update(User $user)
    {
        if ($user->can('edit reasons')) {
            return true;
        }
    }

    public function delete(User $user)
    {
        if ($user->can('delete reasons')) {
            return true;
        }
    }

    public function restore(User $user)
    {
        //
    }

    public function forceDelete(User $user)
    {
        //
    }
}
