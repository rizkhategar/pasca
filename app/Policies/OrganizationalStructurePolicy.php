<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\OrganizationalStructure;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrganizationalStructurePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:OrganizationalStructure');
    }

    public function view(AuthUser $authUser, OrganizationalStructure $organizationalStructure): bool
    {
        return $authUser->can('View:OrganizationalStructure');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:OrganizationalStructure');
    }

    public function update(AuthUser $authUser, OrganizationalStructure $organizationalStructure): bool
    {
        return $authUser->can('Update:OrganizationalStructure');
    }

    public function delete(AuthUser $authUser, OrganizationalStructure $organizationalStructure): bool
    {
        return $authUser->can('Delete:OrganizationalStructure');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:OrganizationalStructure');
    }

    public function restore(AuthUser $authUser, OrganizationalStructure $organizationalStructure): bool
    {
        return $authUser->can('Restore:OrganizationalStructure');
    }

    public function forceDelete(AuthUser $authUser, OrganizationalStructure $organizationalStructure): bool
    {
        return $authUser->can('ForceDelete:OrganizationalStructure');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:OrganizationalStructure');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:OrganizationalStructure');
    }

    public function replicate(AuthUser $authUser, OrganizationalStructure $organizationalStructure): bool
    {
        return $authUser->can('Replicate:OrganizationalStructure');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:OrganizationalStructure');
    }

}