<?php

namespace App\Policies;

use App\Models\DocumentProperty;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DocumentPropertyPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->can('access admin area')) {
            return true;
        }
    }

    public function view(User $user, DocumentProperty $documentProperty)
    {
        if ($user->can('access admin area')) {
            return true;
        }
    }

    public function create(User $user)
    {
        if ($user->can('access admin area')) {
            return true;
        }
    }

    public function update(User $user, DocumentProperty $documentProperty)
    {
        if ($user->can('access admin area')) {
            return true;
        }
    }

    public function delete(User $user, DocumentProperty $documentProperty)
    {
        if ($user->can('access admin area')) {
            return true;
        }
    }

    public function restore(User $user, DocumentProperty $documentProperty)
    {
        //
    }

    public function forceDelete(User $user, DocumentProperty $documentProperty)
    {
       //
    }
}
