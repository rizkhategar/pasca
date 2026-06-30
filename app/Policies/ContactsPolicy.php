<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Contacts;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ContactsPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $this->allows($authUser, 'ViewAny');
    }

    public function view(AuthUser $authUser, Contacts $contacts): bool
    {
        return $this->allows($authUser, 'View');
    }

    public function create(AuthUser $authUser): bool
    {
        return $this->allows($authUser, 'Create');
    }

    public function update(AuthUser $authUser, Contacts $contacts): bool
    {
        return $this->allows($authUser, 'Update');
    }

    public function delete(AuthUser $authUser, Contacts $contacts): bool
    {
        return $this->allows($authUser, 'Delete');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $this->allows($authUser, 'DeleteAny');
    }

    public function restore(AuthUser $authUser, Contacts $contacts): bool
    {
        return $this->allows($authUser, 'Restore');
    }

    public function forceDelete(AuthUser $authUser, Contacts $contacts): bool
    {
        return $this->allows($authUser, 'ForceDelete');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $this->allows($authUser, 'ForceDeleteAny');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $this->allows($authUser, 'RestoreAny');
    }

    public function replicate(AuthUser $authUser, Contacts $contacts): bool
    {
        return $this->allows($authUser, 'Replicate');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $this->allows($authUser, 'Reorder');
    }

    private function allows(AuthUser $authUser, string $action): bool
    {
        return $authUser->can($action . ':Contact') || $authUser->can($action . ':Contacts');
    }
}
