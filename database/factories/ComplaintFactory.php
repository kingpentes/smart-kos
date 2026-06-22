<?php

namespace Database\Factories;

use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use App\Models\Lease;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Complaint>
 */
class ComplaintFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $lease = Lease::factory()->create();

        return [
            'lease_id' => $lease->id,
            'tenant_id' => $lease->tenant_id,
            'owner_id' => $lease->owner_id,
            'category' => $this->faker->randomElement(['fasilitas_rusak', 'kebersihan', 'keamanan', 'lainnya']),
            'description' => $this->faker->paragraph(),
            'status' => ComplaintStatus::Open,
        ];
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ComplaintStatus::InProgress,
        ]);
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ComplaintStatus::Resolved,
        ]);
    }
}
