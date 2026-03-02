<?php

namespace App\Policies;

use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        if ($user->can('manage permissions')){
            return true;
        }
    }

    public function viewAny(User $user)
    {
        if ($user->can('manage permissions')){
            return true;
        }
    }


    public function view(User $user, Role $role)
    {
        //
    }


    public function create(User $user)
    {
        if ($user->can('manage permissions')){
            return true;
        }
    }

    public function update(User $user, Role $role)
    {
        if ($user->can('manage permissions')){
            return true;
        }
    }

    public function delete(User $user, Role $role)
    {
        if ($user->can('manage permissions')){
            return true;
        }
    }

    public function restore(User $user, Role $role)
    {
        if ($user->can('manage permissions')){
            return true;
        }
    }

    public function forceDelete(User $user, Role $role)
    {
        if ($user->can('manage permissions')){
            return true;
        }
    }
}
