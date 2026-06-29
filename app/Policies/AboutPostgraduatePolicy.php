<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\AboutPostgraduate;
use Illuminate\Auth\Access\HandlesAuthorization;

class AboutPostgraduatePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AboutPostgraduate');
    }

    public function view(AuthUser $authUser, AboutPostgraduate $aboutPostgraduate): bool
    {
        return $authUser->can('View:AboutPostgraduate');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AboutPostgraduate');
    }

    public function update(AuthUser $authUser, AboutPostgraduate $aboutPostgraduate): bool
    {
        return $authUser->can('Update:AboutPostgraduate');
    }

    public function delete(AuthUser $authUser, AboutPostgraduate $aboutPostgraduate): bool
    {
        return $authUser->can('Delete:AboutPostgraduate');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:AboutPostgraduate');
    }

    public function restore(AuthUser $authUser, AboutPostgraduate $aboutPostgraduate): bool
    {
        return $authUser->can('Restore:AboutPostgraduate');
    }

    public function forceDelete(AuthUser $authUser, AboutPostgraduate $aboutPostgraduate): bool
    {
        return $authUser->can('ForceDelete:AboutPostgraduate');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AboutPostgraduate');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AboutPostgraduate');
    }

    public function replicate(AuthUser $authUser, AboutPostgraduate $aboutPostgraduate): bool
    {
        return $authUser->can('Replicate:AboutPostgraduate');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AboutPostgraduate');
    }

}