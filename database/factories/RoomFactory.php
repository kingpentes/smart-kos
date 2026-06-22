<?php

namespace Database\Factories;

use App\Enums\RoomStatus;
use App\Models\BoardingHouse;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'boarding_house_id' => BoardingHouse::factory(),
            'room_number' => (string) $this->faker->unique()->numberBetween(1, 200),
            'price_monthly' => $this->faker->numberBetween(700000, 2500000),
            'status' => RoomStatus::Available,
        ];
    }

    public function occupied(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RoomStatus::Occupied,
        ]);
    }
}
