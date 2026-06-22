<?php

namespace App\Actions\BoardingHouses;

use App\Enums\BoardingHouseStatus;
use App\Enums\RoomStatus;
use App\Models\BoardingHouse;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StoreBoardingHouse
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(User $owner, array $data): BoardingHouse
    {
        return DB::transaction(function () use ($owner, $data): BoardingHouse {
            $boardingHouse = BoardingHouse::query()->create([
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
                'owner_id' => $owner->id,
                'slug' => $this->uniqueSlug($data['name']),
                'deposit_amount' => $data['deposit_amount'] ?? 0,
                'status' => BoardingHouseStatus::Draft,
            ]);

            $boardingHouse->facilities()->sync($data['facilities'] ?? []);

            for ($roomNumber = 1; $roomNumber <= (int) $data['room_count']; $roomNumber++) {
                $boardingHouse->rooms()->create([
                    'room_number' => (string) $roomNumber,
                    'price_monthly' => $data['price_monthly'],
                    'status' => RoomStatus::Available,
                ]);
            }

            $this->syncRules($boardingHouse, $data['rules'] ?? []);
            $this->storePhotos($boardingHouse, $data['photos'] ?? []);

            return $boardingHouse;
        });
    }

    private function uniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $suffix = 2;

        while (BoardingHouse::query()->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /**
     * @param  array<int, array{key: string, value: string}>  $rules
     */
    private function syncRules(BoardingHouse $boardingHouse, array $rules): void
    {
        foreach ($rules as $rule) {
            if (blank($rule['key'] ?? null) || blank($rule['value'] ?? null)) {
                continue;
            }

            $boardingHouse->rules()->create($rule);
        }
    }

    /**
     * @param  array<int, UploadedFile>  $photos
     */
    private function storePhotos(BoardingHouse $boardingHouse, array $photos): void
    {
        foreach ($photos as $index => $photo) {
            $boardingHouse->photos()->create([
                'path' => $photo->store('boarding-houses', 'public'),
                'is_primary' => $index === 0,
                'sort_order' => $index,
            ]);
        }
    }
}
