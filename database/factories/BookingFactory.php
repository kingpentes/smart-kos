<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\BoardingHouse;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $boardingHouse = BoardingHouse::factory()->published();

        return [
            'boarding_house_id' => $boardingHouse,
            'room_id' => Room::factory()->for($boardingHouse),
            'tenant_id' => User::factory()->tenant(),
            'start_date' => now()->addDays(7)->toDateString(),
            'duration_months' => 1,
            'status' => BookingStatus::Pending,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BookingStatus::Accepted,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BookingStatus::Rejected,
        ]);
    }
}
