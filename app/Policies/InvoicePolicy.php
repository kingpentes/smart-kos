<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isRole(UserRole::Owner) || $user->isRole(UserRole::Tenant);
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $invoice->isOwnedByTenant($user) || $invoice->isOwnedByOwner($user);
    }

    public function create(User $user): bool
    {
        return $user->isRole(UserRole::Owner);
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $invoice->isOwnedByOwner($user);
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return false;
    }

    public function restore(User $user, Invoice $invoice): bool
    {
        return false;
    }

    public function forceDelete(User $user, Invoice $invoice): bool
    {
        return false;
    }

    public function markPaid(User $user, Invoice $invoice): bool
    {
        return $invoice->isOwnedByOwner($user);
    }
}
