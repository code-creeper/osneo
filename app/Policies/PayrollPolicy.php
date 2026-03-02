<?php

namespace App\Policies;

use App\Models\Payroll;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PayrollPolicy
{
    use HandlesAuthorization;

    //todo::add policies
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Payroll $payroll): bool
    {
        return true;
    }

    public function create(User $user): void
    {
    }

    public function update(User $user, Payroll $payroll): void
    {
    }

    public function delete(User $user, Payroll $payroll): void
    {
    }

    public function restore(User $user, Payroll $payroll): void
    {
    }

    public function forceDelete(User $user, Payroll $payroll): void
    {
    }
}
