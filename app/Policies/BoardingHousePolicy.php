<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\BoardingHouse;
use App\Models\User;

class BoardingHousePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isRole(UserRole::Owner) || $user->isRole(UserRole::Admin);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BoardingHouse $boardingHouse): bool
    {
        return $boardingHouse->isOwnedBy($user) || $user->isRole(UserRole::Admin);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isRole(UserRole::Owner);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BoardingHouse $boardingHouse): bool
    {
        return $boardingHouse->isOwnedBy($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BoardingHouse $boardingHouse): bool
    {
        return $boardingHouse->isOwnedBy($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, BoardingHouse $boardingHouse): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, BoardingHouse $boardingHouse): bool
    {
        return false;
    }

    public function submit(User $user, BoardingHouse $boardingHouse): bool
    {
        return $boardingHouse->isOwnedBy($user);
    }

    public function verify(User $user): bool
    {
        return $user->isRole(UserRole::Admin);
    }
}
