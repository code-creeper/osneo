<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TicketPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->can('view tickets')) {
            return true;
        }
    }

    public function view(User $user, Ticket $ticket)
    {
        if ($user->can('view tickets')) {
            return true;
        }
    }

    public function create(User $user)
    {
        if ($user->can('create tickets')) {
            return true;
        }
    }

    public function update(User $user, Ticket $ticket)
    {
        if ($user->can('edit tickets')) {
            return true;
        }
    }

    public function delete(User $user, Ticket $ticket)
    {
        if ($user->can('delete tickets')) {
            return true;
        }
    }

    public function restore(User $user, Ticket $ticket)
    {
    }

    public function forceDelete(User $user, Ticket $ticket)
    {
    }
}
