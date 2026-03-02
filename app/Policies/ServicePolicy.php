<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServicePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->can('view services')) {
            return true;
        }
    }

    public function view(User $user, Service $service)
    {
        if ($user->can('view services')) {
            return true;
        }
    }

    public function create(User $user)
    {
        if ($user->can('create services')) {
            return true;
        }
    }

    public function update(User $user, Service $service)
    {
        if ($user->can('edit services')) {
            return true;
        }
    }

    public function delete(User $user, Service $service)
    {
        if ($user->can('delete services')) {
            return true;
        }
    }

    public function restore(User $user, Service $service)
    {
    }

    public function forceDelete(User $user, Service $service)
    {
    }
}
