<?php

namespace App\Models;

use Database\Factories\BoardingHousePhotoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BoardingHousePhoto extends Model
{
    /** @use HasFactory<BoardingHousePhotoFactory> */
    use HasFactory;

    protected $fillable = [
        'boarding_house_id',
        'path',
        'caption',
        'is_primary',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function boardingHouse(): BelongsTo
    {
        return $this->belongsTo(BoardingHouse::class);
    }

    public function url(): string
    {
        return Storage::url($this->path);
    }
}
