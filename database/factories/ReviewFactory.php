<?php

namespace Database\Factories;

use App\Models\Lease;
use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lease_id' => Lease::factory(),
            'boarding_house_id' => fn (array $attributes): int => Lease::query()->findOrFail($attributes['lease_id'])->boarding_house_id,
            'tenant_id' => fn (array $attributes): int => Lease::query()->findOrFail($attributes['lease_id'])->tenant_id,
            'cleanliness_rating' => $this->faker->numberBetween(3, 5),
            'security_rating' => $this->faker->numberBetween(3, 5),
            'photo_match_rating' => $this->faker->numberBetween(3, 5),
            'comment' => $this->faker->optional()->paragraph(),
        ];
    }
}
