<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AnnouncementPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->can('access admin area')){
            return true;
        }
    }

    public function view(User $user, Announcement $announcement)
    {
        if ($user->can('create announcements')) {
            return true;
        }
    }

    public function create(User $user)
    {
        if ($user->can('create announcements')){
            return true;
        }
    }

    public function update(User $user, Announcement $announcement)
    {
        if ($user->can('create announcements')){
            return true;
        }
    }

    public function delete(User $user, Announcement $announcement)
    {
        if ($user->can('create announcements')){
            return true;
        }
    }

    public function restore(User $user, Announcement $announcement)
    {
        //
    }

    public function forceDelete(User $user, Announcement $announcement)
    {
        //
    }
}
