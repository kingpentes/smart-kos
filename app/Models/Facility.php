<?php

namespace App\Models;

use Database\Factories\FacilityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Facility extends Model
{
    /** @use HasFactory<FacilityFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function boardingHouses(): BelongsToMany
    {
        return $this->belongsToMany(BoardingHouse::class)->withTimestamps();
    }
}
