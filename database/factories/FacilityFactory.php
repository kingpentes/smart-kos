<?php

namespace Database\Factories;

use App\Models\Facility;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Facility>
 */
class FacilityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->randomElement([
            'WiFi',
            'AC',
            'Kamar Mandi Dalam',
            'Parkir Motor',
            'Dapur Bersama',
            'Laundry',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
