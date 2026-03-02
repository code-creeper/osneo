<?php

namespace App\Policies;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContactPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->can('view contacts')) {
            return true;
        }
    }

    public function view(User $user, Contact $contact)
    {
        if ($user->can('view contacts')) {
            return true;
        }
    }

    public function create(User $user)
    {
        if ($user->can('create contacts')) {
            return true;
        }
    }

    public function update(User $user, Contact $contact)
    {
        if ($user->can('edit contacts')) {
            return true;
        }
    }

    public function delete(User $user, Contact $contact)
    {
        if ($user->can('delete contacts')) {
            return true;
        }
    }

    public function restore(User $user, Contact $contact)
    {
    }

    public function forceDelete(User $user, Contact $contact)
    {
    }
}
