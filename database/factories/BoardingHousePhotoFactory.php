<?php

namespace Database\Factories;

use App\Models\BoardingHouse;
use App\Models\BoardingHousePhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BoardingHousePhoto>
 */
class BoardingHousePhotoFactory extends Factory
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
            'path' => 'boarding-houses/sample-'.$this->faker->numberBetween(1, 5).'.jpg',
            'caption' => $this->faker->optional()->sentence(3),
            'is_primary' => false,
            'sort_order' => $this->faker->numberBetween(1, 10),
        ];
    }

    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => true,
            'sort_order' => 0,
        ]);
    }
}
