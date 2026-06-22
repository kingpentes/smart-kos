<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\BoardingHouse;
use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isRole(UserRole::Owner) || $user->isRole(UserRole::Tenant);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Booking $booking): bool
    {
        return $booking->tenant_id === $user->id || $booking->isOwnedByOwner($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isRole(UserRole::Tenant);
    }

    public function createFor(User $user, BoardingHouse $boardingHouse): bool
    {
        return $user->isRole(UserRole::Tenant)
            && $boardingHouse->owner_id !== $user->id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Booking $booking): bool
    {
        return $booking->isOwnedByOwner($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Booking $booking): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Booking $booking): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Booking $booking): bool
    {
        return false;
    }

    public function accept(User $user, Booking $booking): bool
    {
        return $booking->isOwnedByOwner($user);
    }

    public function reject(User $user, Booking $booking): bool
    {
        return $booking->isOwnedByOwner($user);
    }
}
