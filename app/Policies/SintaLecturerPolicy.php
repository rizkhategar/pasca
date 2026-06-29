<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SintaLecturer;
use Illuminate\Auth\Access\HandlesAuthorization;

class SintaLecturerPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SintaLecturer');
    }

    public function view(AuthUser $authUser, SintaLecturer $sintaLecturer): bool
    {
        return $authUser->can('View:SintaLecturer');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SintaLecturer');
    }

    public function update(AuthUser $authUser, SintaLecturer $sintaLecturer): bool
    {
        return $authUser->can('Update:SintaLecturer');
    }

    public function delete(AuthUser $authUser, SintaLecturer $sintaLecturer): bool
    {
        return $authUser->can('Delete:SintaLecturer');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SintaLecturer');
    }

    public function restore(AuthUser $authUser, SintaLecturer $sintaLecturer): bool
    {
        return $authUser->can('Restore:SintaLecturer');
    }

    public function forceDelete(AuthUser $authUser, SintaLecturer $sintaLecturer): bool
    {
        return $authUser->can('ForceDelete:SintaLecturer');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SintaLecturer');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SintaLecturer');
    }

    public function replicate(AuthUser $authUser, SintaLecturer $sintaLecturer): bool
    {
        return $authUser->can('Replicate:SintaLecturer');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SintaLecturer');
    }

}