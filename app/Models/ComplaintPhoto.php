<?php

namespace App\Models;

use Database\Factories\ComplaintPhotoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintPhoto extends Model
{
    /** @use HasFactory<ComplaintPhotoFactory> */
    use HasFactory;

    protected $fillable = [
        'complaint_id',
        'path',
    ];

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }
}
