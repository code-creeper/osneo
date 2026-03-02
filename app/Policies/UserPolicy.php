<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function __construct()
    {
        //
    }

    /**
     * Determine whether the user can view any users.
     *
     * @param  User  $user
     *
     * @return bool
     */
    public function viewAny(User $user)
    {
        if ($user->can('view users')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the user.
     *
     * @param  User  $user
     * @param  User  $model
     *
     * @return bool
     */
    public function view(User $user, User $model)
    {
        if ($user->can('view users')) {
            return true;
        }
    }

    /**
     * Determine whether the user can create users.
     *
     * @param  User  $user
     *
     * @return bool
     */
    public function create(User $user)
    {
        if ($user->can('create users')) {
            return true;
        }
    }

    /**
     * Determine whether the user can update the user.
     *
     * @param  User  $user
     * @param  User  $model
     *
     * @return bool
     */
    public function update(User $user, User $model)
    {
        if ($user->can('edit users')) {
            return true;
        }
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  User  $user
     * @param  User  $model
     *
     * @return bool
     */
    public function delete(User $user, User $model)
    {
        if ($model->isSystem()){
            return false;
        }

        if ($user->can('delete users')) {
            return true;
        }
    }

    /**
     * Determine whether the user can restore the user.
     *
     * @param  User  $user
     * @param  User  $model
     *
     * @return bool
     */
    public function restore(User $user, User $model)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the user.
     *
     * @param  User  $user
     * @param  User  $model
     *
     * @return bool
     */
    public function forceDelete(User $user, User $model)
    {
        //
    }

    public function json(User $user)
    {
        if ($user->can('view users')) {
            return true;
        }
    }
}
