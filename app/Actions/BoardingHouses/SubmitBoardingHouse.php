<?php

namespace App\Actions\BoardingHouses;

use App\Enums\BoardingHouseStatus;
use App\Models\BoardingHouse;

class SubmitBoardingHouse
{
    public function handle(BoardingHouse $boardingHouse): BoardingHouse
    {
        $boardingHouse->update([
            'status' => BoardingHouseStatus::Pending,
            'verified_at' => null,
            'verified_by' => null,
        ]);

        return $boardingHouse->refresh();
    }
}
