<?php

namespace App\Policies;

use App\Models\Constant;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ConstantPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->can('view constants')) {
            return true;
        }
    }

    public function view(User $user, Constant $contact)
    {
        if ($user->can('view constants')) {
            return true;
        }
    }

    public function create(User $user)
    {
        if ($user->can('create constants')) {
            return true;
        }
    }

    public function update(User $user, Constant $contact)
    {
        if ($user->can('edit constants')) {
            return true;
        }
    }

    public function delete(User $user, Constant $contact)
    {
        if ($user->can('delete constants')) {
            return true;
        }
    }
}
