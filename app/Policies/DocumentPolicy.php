<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DocumentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->canAny([
            'view all documents',
            'view own documents',
            'view assigned documents',
            'view documents of ticket',
            'sort documents'
        ])) {
            return true;
        }
    }

    public function viewAnySorted(User $user)
    {
        if ($user->canAny([
            'view all documents',
            'view own documents',
            'view assigned documents',
            'view documents of ticket',
        ])) {
            return true;
        }
    }

    public function view(User $user, Document $document)
    {
        if ($user->can('view all documents')) {
            return true;
        }

        if (!$document->sorted){
            return $user->can('sort documents');
        }

        if ($user->can('view own documents')){
            return $user->id == $document->uploaded_by;
        }

        if ($user->can('view assigned documents')){
            return in_array($user->id, $document->users->pluck('id')->toArray());
        }
    }

    public function create(User $user)
    {
        if ($user->can('create documents')) {
            return true;
        }
    }

    public function update(User $user, Document $document)
    {
        if($document->trashed()){
            return false;
        }

        if ($user->can('sort documents')) {
            return true;
        }
    }

    public function delete(User $user, Document $document)
    {
        if($document->trashed() && $user->cannot('forceDelete', $document)){
            return false;
        }

        if ($user->can('delete all documents')) {
            return true;
        }

        if ($user->can('delete own documents')){
            return $document->uploaded_by == $user->id;
        }
    }

    public function assign(User $user, Document $document)
    {
        if($document->trashed()){
            return false;
        }

        if ($user->can('assign documents')) {
            return true;
        }
    }

    public function restore(User $user, Document $document)
    {
        if(!$document->trashed()){
            return false;
        }

        if ($user->can('restore all documents')) {
            return true;
        }

        if ($user->can('restore own documents')){
            return $document->uploaded_by == $user->id;
        }
    }

    public function forceDelete(User $user, Document $document)
    {
        if ($user->can('force delete documents')){
            return true;
        }
    }
}
