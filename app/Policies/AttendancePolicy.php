<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttendancePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->can('view all attendance')) {
            return true;
        }

        if ($user->can('view own attendance')) {
            return true;
        }
    }

    public function view(User $user, Attendance $attendance)
    {
        //
    }

    public function create(User $user)
    {
        if ($user->can('create manual attendance for all')) {
            return true;
        }

        if ($user->can('create manual attendance')) {
            return true;
        }

        if ($user->can('create attendance')) {
            return true;
        }
    }

    public function update(User $user, Attendance $attendance)
    {
        if($attendance->trashed()){
            return false;
        }

        if ($attendance->isActive()){
            return false;
        }

        if ($attendance->pendingModification){
            return false;
        }

        if ($user->can('edit any attendance')) {
            return true;
        }

        if ($user->can('edit own attendance')) {
            return $attendance->user_id == $user->id;
        }
    }

    public function delete(User $user, Attendance $attendance)
    {
        if($attendance->trashed()){
            return false;
        }

        if ($attendance->isActive()){
            return false;
        }

        if ($attendance->pendingModification){
            return false;
        }

        if ($user->can('delete any attendance')) {
            return true;
        }

        if ($user->can('delete own attendance')) {
            return $attendance->user_id == $user->id;
        }
    }

    public function restore(User $user, Attendance $attendance)
    {
        if(!$attendance->trashed()){
            return false;
        }

        if ($attendance->pendingModification){
            return false;
        }

        if ($user->can('restore any attendance')) {
            return true;
        }

        if ($user->can('restore own attendance')) {
            return $attendance->user_id == $user->id;
        }
    }

    public function forceDelete(User $user, Attendance $attendance)
    {
        //
    }

    public function deleteModification(User $user, Attendance $attendance)
    {
        if ( ! $attendance->pendingModification) {
            return false;
        }

        if ($user->can('delete own modifications')) {
            return $attendance->user_id == $user->id;
        }
    }

    public function storeManually(User $user)
    {
        if ($user->can('create manual attendance for all')) {
            return true;
        }

        if ($user->can('create manual attendance')) {
            return true;
        }
    }

    public function calendar(User $user)
    {
        return $this->viewAny($user);
    }

    public function jsonOwn(User $user)
    {
        return $this->viewAny($user);
    }

    public function jsonAll(User $user)
    {
        if ($user->can('view all attendance')) {
            return true;
        }
    }
}
