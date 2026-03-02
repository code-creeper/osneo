<?php

namespace App\Policies;

use App\Models\ManualEntry;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ManualEntryPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->can('view manual entries')) {
            return true;
        }
    }

    public function view(User $user, ManualEntry $entry)
    {
        //
    }

    public function create(User $user)
    {
        if ($user->can('create manual entries')) {
            return true;
        }
    }

    public function update(User $user, ManualEntry $entry)
    {
        if ($user->can('edit manual entries')) {
            return true;
        }
    }

    public function delete(User $user, ManualEntry $entry)
    {
        if ($user->can('delete manual entries')) {
            return true;
        }
    }

    public function restore(User $user, ManualEntry $entry)
    {
        //
    }

    public function forceDelete(User $user, ManualEntry $entry)
    {
        //
    }
}
