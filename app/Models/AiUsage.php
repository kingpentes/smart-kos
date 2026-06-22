<?php

namespace App\Models;

use App\Enums\AiFeature;
use Database\Factories\AiUsageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsage extends Model
{
    /** @use HasFactory<AiUsageFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'feature',
        'source',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'feature' => AiFeature::class,
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
