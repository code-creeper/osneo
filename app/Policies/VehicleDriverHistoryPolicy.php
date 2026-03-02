<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VehicleDriverHistory;
use Illuminate\Auth\Access\HandlesAuthorization;

class VehicleDriverHistoryPolicy
{
    use HandlesAuthorization;

    public function __construct()
    {
        //
    }

    public function viewAny(User $user)
    {
        if ($user->can('view all vehicle histories')){
            return true;
        }

        if ($user->can('view own vehicle histories')){
            return true;
        }
    }

    public function view(User $user, VehicleDriverHistory $vehicleDriverHistory): bool
    {
        if ($user->can('create vehicle histories')){
            return true;
        }
    }

    public function create(User $user)
    {
        if ($user->can('create vehicle histories')){
            return true;
        }
    }

    public function update(User $user, VehicleDriverHistory $vehicleDriverHistory)
    {
        if ($user->can('edit all vehicle histories')){
            return true;
        }

        if ($user->can('edit own vehicle histories')){
            return $vehicleDriverHistory->driver_id == $user->id;
        }
    }

    public function delete(User $user, VehicleDriverHistory $vehicleDriverHistory): bool
    {
        if ($user->can('delete all vehicle histories')){
            return true;
        }

        if ($user->can('delete own vehicle histories')){
            return $vehicleDriverHistory->driver_id == $user->id;
        }
    }

    public function restore(User $user, VehicleDriverHistory $vehicleDriverHistory): bool
    {
        //
    }

    public function forceDelete(User $user, VehicleDriverHistory $vehicleDriverHistory): bool
    {
        //
    }
}
