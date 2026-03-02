<?php

namespace App\Policies;

use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DocumentTypePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->can('access admin area')) {
            return true;
        }
    }

    public function view(User $user, DocumentType $documentType)
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

    public function update(User $user, DocumentType $documentType)
    {
        if ($user->can('access admin area')) {
            return true;
        }
    }

    public function delete(User $user, DocumentType $documentType)
    {
        if ($user->can('access admin area')) {
            return true;
        }
    }

    public function restore(User $user, DocumentType $documentType)
    {
        //
    }

    public function forceDelete(User $user, DocumentType $documentType)
    {
       //
    }
}
