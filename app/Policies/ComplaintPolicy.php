<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Complaint;
use App\Models\User;

class ComplaintPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isRole(UserRole::Tenant) || $user->isRole(UserRole::Owner);
    }

    public function view(User $user, Complaint $complaint): bool
    {
        return $complaint->isOwnedByTenant($user) || $complaint->isOwnedByOwner($user);
    }

    public function create(User $user): bool
    {
        return $user->isRole(UserRole::Tenant);
    }

    public function update(User $user, Complaint $complaint): bool
    {
        return $complaint->isOwnedByOwner($user);
    }

    public function delete(User $user, Complaint $complaint): bool
    {
        return false;
    }

    public function restore(User $user, Complaint $complaint): bool
    {
        return false;
    }

    public function forceDelete(User $user, Complaint $complaint): bool
    {
        return false;
    }

    public function reply(User $user, Complaint $complaint): bool
    {
        return $complaint->isOwnedByTenant($user) || $complaint->isOwnedByOwner($user);
    }

    public function updateStatus(User $user, Complaint $complaint): bool
    {
        return $complaint->isOwnedByOwner($user);
    }
}
