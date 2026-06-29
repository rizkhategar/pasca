<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\VisionMission;
use Illuminate\Auth\Access\HandlesAuthorization;

class VisionMissionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:VisionMission');
    }

    public function view(AuthUser $authUser, VisionMission $visionMission): bool
    {
        return $authUser->can('View:VisionMission');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:VisionMission');
    }

    public function update(AuthUser $authUser, VisionMission $visionMission): bool
    {
        return $authUser->can('Update:VisionMission');
    }

    public function delete(AuthUser $authUser, VisionMission $visionMission): bool
    {
        return $authUser->can('Delete:VisionMission');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:VisionMission');
    }

    public function restore(AuthUser $authUser, VisionMission $visionMission): bool
    {
        return $authUser->can('Restore:VisionMission');
    }

    public function forceDelete(AuthUser $authUser, VisionMission $visionMission): bool
    {
        return $authUser->can('ForceDelete:VisionMission');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:VisionMission');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:VisionMission');
    }

    public function replicate(AuthUser $authUser, VisionMission $visionMission): bool
    {
        return $authUser->can('Replicate:VisionMission');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:VisionMission');
    }

}