<?php

namespace App\Policies;

use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServiceCategoryPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->can('view service categories')) {
            return true;
        }
    }

    public function view(User $user, ServiceCategory $serviceCategory)
    {
        if ($user->can('view service categories')) {
            return true;
        }
    }

    public function create(User $user)
    {
        if ($user->can('create service categories')) {
            return true;
        }
    }

    public function update(User $user, ServiceCategory $serviceCategory)
    {
        if ($user->can('edit service categories')) {
            return true;
        }
    }

    public function delete(User $user, ServiceCategory $serviceCategory)
    {
        if ($user->can('delete service categories')) {
            return true;
        }
    }

    public function restore(User $user, ServiceCategory $serviceCategory)
    {
    }

    public function forceDelete(User $user, ServiceCategory $serviceCategory)
    {
    }
}
