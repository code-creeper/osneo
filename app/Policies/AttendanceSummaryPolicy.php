<?php

namespace App\Policies;

use App\Models\AttendanceSummary;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttendanceSummaryPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('access admin area');
    }

    public function create(User $user): bool
    {
        return $user->can('access admin area');
    }
}
