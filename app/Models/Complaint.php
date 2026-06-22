<?php

namespace App\Models;

use App\Enums\ComplaintStatus;
use Database\Factories\ComplaintFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Complaint extends Model
{
    /** @use HasFactory<ComplaintFactory> */
    use HasFactory;

    protected $fillable = [
        'lease_id',
        'tenant_id',
        'owner_id',
        'category',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => ComplaintStatus::class,
        ];
    }

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ComplaintPhoto::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ComplaintReply::class);
    }

    public function isOwnedByTenant(User $tenant): bool
    {
        return $this->tenant_id === $tenant->id;
    }

    public function isOwnedByOwner(User $owner): bool
    {
        return $this->owner_id === $owner->id;
    }
}
