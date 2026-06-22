<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Lease;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $periodStart = now()->startOfMonth();

        return [
            'lease_id' => Lease::factory(),
            'number' => 'INV-'.$this->faker->unique()->numerify('########'),
            'period_start' => $periodStart->toDateString(),
            'period_end' => $periodStart->copy()->addMonthNoOverflow()->subDay()->toDateString(),
            'due_date' => $periodStart->copy()->addDays(7)->toDateString(),
            'amount' => $this->faker->numberBetween(700000, 2500000),
            'status' => InvoiceStatus::Unpaid,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InvoiceStatus::Paid,
        ]);
    }
}
