<?php

namespace Database\Factories;

use App\Enums\BoardingHouseStatus;
use App\Enums\BoardingHouseType;
use App\Models\BoardingHouse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BoardingHouse>
 */
class BoardingHouseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Kos '.$this->faker->streetName();

        return [
            'owner_id' => User::factory()->owner(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.$this->faker->unique()->numberBetween(1000, 9999),
            'description' => $this->faker->paragraph(),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->randomElement(['Bandung', 'Jakarta', 'Yogyakarta']),
            'district' => $this->faker->randomElement(['Sukapura', 'Cengkareng', 'Depok']),
            'type' => $this->faker->randomElement(BoardingHouseType::cases()),
            'latitude' => $this->faker->latitude(-8, -5),
            'longitude' => $this->faker->longitude(106, 111),
            'price_monthly' => $this->faker->numberBetween(700000, 2500000),
            'deposit_amount' => $this->faker->numberBetween(0, 1000000),
            'status' => BoardingHouseStatus::Draft,
            'verified_at' => null,
            'verified_by' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BoardingHouseStatus::Pending,
            'verified_at' => null,
            'verified_by' => null,
        ]);
    }

    public function published(?User $admin = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BoardingHouseStatus::Published,
            'verified_at' => now(),
            'verified_by' => $admin?->id,
        ]);
    }

    public function rejected(?User $admin = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BoardingHouseStatus::Rejected,
            'verified_at' => null,
            'verified_by' => $admin?->id,
        ]);
    }
}
