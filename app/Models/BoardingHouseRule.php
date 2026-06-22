<?php

namespace App\Models;

use Database\Factories\BoardingHouseRuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoardingHouseRule extends Model
{
    /** @use HasFactory<BoardingHouseRuleFactory> */
    use HasFactory;

    protected $fillable = [
        'boarding_house_id',
        'key',
        'value',
    ];

    public function boardingHouse(): BelongsTo
    {
        return $this->belongsTo(BoardingHouse::class);
    }
}
