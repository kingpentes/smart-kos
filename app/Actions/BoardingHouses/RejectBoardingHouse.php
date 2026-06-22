<?php

namespace App\Actions\BoardingHouses;

use App\Enums\BoardingHouseStatus;
use App\Models\BoardingHouse;
use App\Models\User;

class RejectBoardingHouse
{
    public function handle(BoardingHouse $boardingHouse, User $admin): BoardingHouse
    {
        $boardingHouse->update([
            'status' => BoardingHouseStatus::Rejected,
            'verified_at' => null,
            'verified_by' => $admin->id,
        ]);

        return $boardingHouse->refresh();
    }
}
