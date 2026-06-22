<?php

namespace App\Actions\BoardingHouses;

use App\Enums\BoardingHouseStatus;
use App\Models\BoardingHouse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UpdateBoardingHouse
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(BoardingHouse $boardingHouse, array $data): BoardingHouse
    {
        return DB::transaction(function () use ($boardingHouse, $data): BoardingHouse {
            $boardingHouse->update([
                ...Arr::only($data, [
                    'name',
                    'description',
                    'address',
                    'city',
                    'district',
                    'type',
                    'latitude',
                    'longitude',
                    'price_monthly',
                    'deposit_amount',
                ]),
                'deposit_amount' => $data['deposit_amount'] ?? 0,
                'status' => $boardingHouse->status === BoardingHouseStatus::Published
                    ? BoardingHouseStatus::Pending
                    : $boardingHouse->status,
                'verified_at' => null,
                'verified_by' => null,
            ]);

            $boardingHouse->facilities()->sync($data['facilities'] ?? []);
            $boardingHouse->rules()->delete();

            foreach ($data['rules'] ?? [] as $rule) {
                if (blank($rule['key'] ?? null) || blank($rule['value'] ?? null)) {
                    continue;
                }

                $boardingHouse->rules()->create($rule);
            }

            foreach ($data['photos'] ?? [] as $index => $photo) {
                $boardingHouse->photos()->create([
                    'path' => $photo->store('boarding-houses', 'public'),
                    'is_primary' => ! $boardingHouse->photos()->exists() && $index === 0,
                    'sort_order' => $boardingHouse->photos()->count() + $index,
                ]);
            }

            return $boardingHouse->refresh();
        });
    }
}
