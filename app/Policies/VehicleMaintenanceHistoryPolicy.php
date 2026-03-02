<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VehicleMaintenanceHistory;
use Illuminate\Auth\Access\HandlesAuthorization;

class VehicleMaintenanceHistoryPolicy
{
    use HandlesAuthorization;

    public function __construct()
    {
        //
    }

    public function viewAny(User $user)
    {
        //
    }

    public function view(
        User $user,
        VehicleMaintenanceHistory $vehicleCondition
    ) {
        //
    }

    public function create(User $user)
    {
        //
    }

    public function update(
        User $user,
        VehicleMaintenanceHistory $vehicleCondition
    ) {
        //
    }

    public function delete(
        User $user,
        VehicleMaintenanceHistory $vehicleCondition
    ) {
        //
    }

    public function restore(
        User $user,
        VehicleMaintenanceHistory $vehicleCondition
    ) {
        //
    }

    public function forceDelete(
        User $user,
        VehicleMaintenanceHistory $vehicleCondition
    ) {
        //
    }
}
