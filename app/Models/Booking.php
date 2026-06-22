<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    protected $fillable = [
        'boarding_house_id',
        'room_id',
        'tenant_id',
        'start_date',
        'duration_months',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'duration_months' => 'integer',
            'status' => BookingStatus::class,
        ];
    }

    public function boardingHouse(): BelongsTo
    {
        return $this->belongsTo(BoardingHouse::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function lease(): HasOne
    {
        return $this->hasOne(Lease::class);
    }

    public function invoice(): HasOneThrough
    {
        return $this->hasOneThrough(Invoice::class, Lease::class);
    }

    public function isOwnedByOwner(User $owner): bool
    {
        return $this->boardingHouse?->owner_id === $owner->id;
    }
}
