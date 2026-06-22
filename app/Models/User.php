<?php

namespace App\Models;

use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'email_verified_at', 'password', 'role', 'phone', 'status', 'ai_trial_credits_remaining', 'google_id', 'google_avatar'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'status' => UserStatus::class,
            'ai_trial_credits_remaining' => 'integer',
        ];
    }

    public function boardingHouses(): HasMany
    {
        return $this->hasMany(BoardingHouse::class, 'owner_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'tenant_id');
    }

    public function leases(): HasMany
    {
        return $this->hasMany(Lease::class, 'tenant_id');
    }

    public function ownedLeases(): HasMany
    {
        return $this->hasMany(Lease::class, 'owner_id');
    }

    public function tenantInvoices(): HasManyThrough
    {
        return $this->hasManyThrough(Invoice::class, Lease::class, 'tenant_id', 'lease_id');
    }

    public function ownerInvoices(): HasManyThrough
    {
        return $this->hasManyThrough(Invoice::class, Lease::class, 'owner_id', 'lease_id');
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'tenant_id');
    }

    public function ownedComplaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'owner_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'tenant_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function subscriptionPayments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function aiUsages(): HasMany
    {
        return $this->hasMany(AiUsage::class);
    }

    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->where('role', $this->role->value)
            ->where('status', SubscriptionStatus::Active->value)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>', now())
            ->latest('ends_at')
            ->first();
    }

    public function isRole(UserRole $role): bool
    {
        return $this->role === $role;
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }
}
