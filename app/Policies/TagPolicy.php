<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TagPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->can('view tags')) {
            return true;
        }
    }

    public function view(User $user)
    {
        //
    }

    public function create(User $user)
    {
        if ($user->can('create tags')) {
            return true;
        }
    }

    public function update(User $user)
    {
        if ($user->can('edit tags')) {
            return true;
        }
    }

    public function delete(User $user)
    {
        if ($user->can('delete tags')) {
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
