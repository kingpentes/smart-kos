<?php

namespace App\Models;

use Database\Factories\BillingReminderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingReminder extends Model
{
    /** @use HasFactory<BillingReminderFactory> */
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'channel',
        'reminder_date',
        'days_before_due',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'reminder_date' => 'date',
            'days_before_due' => 'integer',
            'sent_at' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
