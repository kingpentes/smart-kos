<?php

namespace Database\Factories;

use App\Enums\LeaseStatus;
use App\Models\BoardingHouse;
use App\Models\Booking;
use App\Models\Lease;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lease>
 */
class LeaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $owner = User::factory()->owner();
        $boardingHouse = BoardingHouse::factory()->published()->for($owner, 'owner');
        $startDate = now()->addDays(7);

        return [
            'booking_id' => Booking::factory(),
            'boarding_house_id' => $boardingHouse,
            'room_id' => Room::factory()->for($boardingHouse),
            'tenant_id' => User::factory()->tenant(),
            'owner_id' => $owner,
            'start_date' => $startDate->toDateString(),
            'end_date' => $startDate->copy()->addMonth()->subDay()->toDateString(),
            'next_due_date' => $startDate->copy()->addMonth()->toDateString(),
            'status' => LeaseStatus::Active,
        ];
    }
}
