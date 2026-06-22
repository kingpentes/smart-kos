<?php

namespace App\Actions\BoardingHouses;

use App\Enums\BoardingHouseStatus;
use App\Models\BoardingHouse;
use App\Models\User;

class VerifyBoardingHouse
{
    public function handle(BoardingHouse $boardingHouse, User $admin): BoardingHouse
    {
        $boardingHouse->update([
            'status' => BoardingHouseStatus::Published,
            'verified_at' => now(),
            'verified_by' => $admin->id,
        ]);

        return $boardingHouse->refresh();
    }
}
