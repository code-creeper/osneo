<?php

namespace App\Policies;

use App\Models\Damage;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DamagePolicy
{
    use HandlesAuthorization;

    public function __construct()
    {
        //
    }

    public function viewAny(User $user)
    {
        if ($user->can('view all damages') || $user->can('view own damages')) {
            return true;
        }
    }

    public function view(User $user, Damage $damage)
    {
        if ($user->can('view all damages') || $user->can('view own damages')) {
            return true;
        }
    }

    public function create(User $user)
    {
        if ($user->can('create damages')) {
            return true;
        }
    }

    public function update(User $user, Damage $damage)
    {
        if ($user->can('edit all damages') || $user->can('edit own damages')) {
            return true;
        }
    }

    public function delete(User $user, Damage $damage)
    {
        if ($user->can('delete all damages') || $user->can('delete own damages')) {
            return true;
        }
    }

    public function restore(User $user, Damage $damage)
    {
        //
    }

    public function forceDelete(User $user, Damage $damage)
    {
        //
    }
}
