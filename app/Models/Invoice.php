<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory;

    protected $fillable = [
        'lease_id',
        'number',
        'title',
        'description',
        'period_start',
        'period_end',
        'due_date',
        'amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'due_date' => 'date',
            'amount' => 'integer',
            'status' => InvoiceStatus::class,
        ];
    }

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function billingReminders(): HasMany
    {
        return $this->hasMany(BillingReminder::class);
    }

    public function isOwnedByTenant(User $tenant): bool
    {
        return $this->lease?->tenant_id === $tenant->id;
    }

    public function isOwnedByOwner(User $owner): bool
    {
        return $this->lease?->owner_id === $owner->id;
    }
}
