<?php

namespace App\Models;

use App\Enums\BoardingHouseStatus;
use App\Enums\BoardingHouseType;
use App\Enums\RoomStatus;
use Database\Factories\BoardingHouseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BoardingHouse extends Model
{
    /** @use HasFactory<BoardingHouseFactory> */
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'name',
        'slug',
        'description',
        'address',
        'city',
        'district',
        'type',
        'latitude',
        'longitude',
        'price_monthly',
        'deposit_amount',
        'status',
        'verified_at',
        'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => BoardingHouseType::class,
            'status' => BoardingHouseStatus::class,
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'price_monthly' => 'integer',
            'deposit_amount' => 'integer',
            'verified_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function availableRooms(): HasMany
    {
        return $this->rooms()->where('status', RoomStatus::Available->value);
    }

    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(Facility::class)->withTimestamps();
    }

    public function photos(): HasMany
    {
        return $this->hasMany(BoardingHousePhoto::class)->orderBy('sort_order');
    }

    public function primaryPhoto(): HasOne
    {
        return $this->hasOne(BoardingHousePhoto::class)->where('is_primary', true);
    }

    public function rules(): HasMany
    {
        return $this->hasMany(BoardingHouseRule::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function leases(): HasMany
    {
        return $this->hasMany(Lease::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->latest();
    }

    public function trustScore(): ?float
    {
        if (! $this->relationLoaded('reviews')) {
            return null;
        }

        if ($this->reviews->isEmpty()) {
            return null;
        }

        return round($this->reviews->avg(fn (Review $review): float => $review->averageRating()), 1);
    }

    public function scopePublished($query)
    {
        return $query->where('status', BoardingHouseStatus::Published->value);
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->owner_id === $user->id;
    }
}
