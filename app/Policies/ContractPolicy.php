<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContractPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->can('view contracts')) {
            return true;
        }
    }

    public function view(User $user, Contract $contract)
    {
        if ($user->can('view contracts')) {
            return true;
        }
    }

    public function create(User $user)
    {
        if ($user->can('create contracts')) {
            return true;
        }
    }

    public function update(User $user, Contract $contract)
    {
        if ($user->can('edit contracts')) {
            return true;
        }
    }

    public function delete(User $user, Contract $contract)
    {
        if ($user->can('delete contracts')) {
            return true;
        }
    }

    public function restore(User $user, Contract $contract)
    {
    }

    public function forceDelete(User $user, Contract $contract)
    {
    }
}
