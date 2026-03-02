<?php

namespace App\Policies;

use App\Models\Modification;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ModificationPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->can('view all modifications')
            || $user->can('view own modifications')
        ) {
            return true;
        }
    }

    public function view(User $user, Modification $modification)
    {
        //
    }

    public function create(User $user)
    {
        //
    }

    public function update(User $user, Modification $modification)
    {
        if ($user->can('approve modifications')){
            return true;
        }
    }

    public function delete(User $user, Modification $modification)
    {
        if ($user->can('delete all modifications')){
            return true;
        }

        if ($user->can('delete own modifications')){
            return $modification->user_id === $user->id;
        }
    }

    public function restore(User $user, Modification $modification)
    {
        //
    }

    public function forceDelete(User $user, Modification $modification)
    {
        //
    }
}
