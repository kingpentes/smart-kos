<?php

namespace Database\Factories;

use App\Models\BoardingHouse;
use App\Models\BoardingHouseRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BoardingHouseRule>
 */
class BoardingHouseRuleFactory extends Factory
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
            'key' => $this->faker->unique()->randomElement(['Jam malam', 'Tamu', 'Hewan peliharaan']),
            'value' => $this->faker->sentence(),
        ];
    }
}
