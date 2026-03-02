<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Auth\Access\HandlesAuthorization;

class VehiclePolicy
{
    use HandlesAuthorization;

    public function __construct()
    {
        //
    }

    public function viewAny(User $user)
    {
        if ($user->can('view vehicles')) {
            return true;
        }
    }

    public function view(User $user, Vehicle $vehicle)
    {
        if ($user->can('view vehicles')) {
            return true;
        }
    }

    public function create(User $user)
    {
        if ($user->can('create vehicles')) {
            return true;
        }
    }

    public function update(User $user, Vehicle $vehicle)
    {
        if ($user->can('edit vehicles')) {
            return true;
        }
    }

    public function delete(User $user, Vehicle $vehicle)
    {
        if ($user->can('delete vehicles')) {
            return true;
        }
    }

    public function restore(User $user, Vehicle $vehicle)
    {
        //
    }

    public function forceDelete(User $user, Vehicle $vehicle)
    {
        //
    }
}
