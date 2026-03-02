<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VehicleSelection;
use Illuminate\Auth\Access\HandlesAuthorization;

class VehicleSelectionPolicy
{
    use HandlesAuthorization;

    public function __construct()
    {
        //
    }

    public function viewAny(User $user): bool
    {
        //
    }

    public function view(User $user, VehicleSelection $vehicleSelection): bool
    {
        //
    }

    public function create(User $user): bool
    {
        //
    }

    public function update(User $user, VehicleSelection $vehicleSelection): bool
    {
        //
    }

    public function delete(User $user, VehicleSelection $vehicleSelection): bool
    {
        //
    }

    public function restore(User $user, VehicleSelection $vehicleSelection): bool
    {
        //
    }

    public function forceDelete(
        User $user,
        VehicleSelection $vehicleSelection
    ): bool {
        //
    }
}
