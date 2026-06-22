<?php

namespace App\Models;

use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    /** @use HasFactory<ReviewFactory> */
    use HasFactory;

    protected $fillable = [
        'lease_id',
        'boarding_house_id',
        'tenant_id',
        'cleanliness_rating',
        'security_rating',
        'photo_match_rating',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'cleanliness_rating' => 'integer',
            'security_rating' => 'integer',
            'photo_match_rating' => 'integer',
        ];
    }

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    public function boardingHouse(): BelongsTo
    {
        return $this->belongsTo(BoardingHouse::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function averageRating(): float
    {
        return round(($this->cleanliness_rating + $this->security_rating + $this->photo_match_rating) / 3, 1);
    }

    public function isOwnedByTenant(User $user): bool
    {
        return $this->tenant_id === $user->id;
    }
}
