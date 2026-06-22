<?php

namespace App\Actions\Reviews;

use App\Enums\LeaseStatus;
use App\Models\Lease;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateReview
{
    /**
     * @param  array{
     *     lease_id: int,
     *     cleanliness_rating: int,
     *     security_rating: int,
     *     photo_match_rating: int,
     *     comment?: string|null
     * }  $data
     *
     * @throws ValidationException
     */
    public function handle(User $tenant, array $data): Review
    {
        return DB::transaction(function () use ($tenant, $data): Review {
            $lease = Lease::query()
                ->whereKey($data['lease_id'])
                ->where('tenant_id', $tenant->id)
                ->whereIn('status', [LeaseStatus::Active->value, LeaseStatus::Ended->value])
                ->with('review')
                ->first();

            if (! $lease) {
                throw ValidationException::withMessages([
                    'lease_id' => 'Sewa aktif atau selesai tidak ditemukan.',
                ]);
            }

            if ($lease->review) {
                throw ValidationException::withMessages([
                    'lease_id' => 'Sewa ini sudah pernah diberi ulasan.',
                ]);
            }

            return Review::query()->create([
                'lease_id' => $lease->id,
                'boarding_house_id' => $lease->boarding_house_id,
                'tenant_id' => $tenant->id,
                'cleanliness_rating' => $data['cleanliness_rating'],
                'security_rating' => $data['security_rating'],
                'photo_match_rating' => $data['photo_match_rating'],
                'comment' => $data['comment'] ?? null,
            ]);
        });
    }
}
