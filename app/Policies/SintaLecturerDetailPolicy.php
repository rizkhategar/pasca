<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SintaLecturerDetail;
use Illuminate\Auth\Access\HandlesAuthorization;

class SintaLecturerDetailPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SintaLecturerDetail');
    }

    public function view(AuthUser $authUser, SintaLecturerDetail $sintaLecturerDetail): bool
    {
        return $authUser->can('View:SintaLecturerDetail');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SintaLecturerDetail');
    }

    public function update(AuthUser $authUser, SintaLecturerDetail $sintaLecturerDetail): bool
    {
        return $authUser->can('Update:SintaLecturerDetail');
    }

    public function delete(AuthUser $authUser, SintaLecturerDetail $sintaLecturerDetail): bool
    {
        return $authUser->can('Delete:SintaLecturerDetail');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SintaLecturerDetail');
    }

    public function restore(AuthUser $authUser, SintaLecturerDetail $sintaLecturerDetail): bool
    {
        return $authUser->can('Restore:SintaLecturerDetail');
    }

    public function forceDelete(AuthUser $authUser, SintaLecturerDetail $sintaLecturerDetail): bool
    {
        return $authUser->can('ForceDelete:SintaLecturerDetail');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SintaLecturerDetail');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SintaLecturerDetail');
    }

    public function replicate(AuthUser $authUser, SintaLecturerDetail $sintaLecturerDetail): bool
    {
        return $authUser->can('Replicate:SintaLecturerDetail');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SintaLecturerDetail');
    }

}