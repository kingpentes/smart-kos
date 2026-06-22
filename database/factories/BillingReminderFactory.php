<?php

namespace Database\Factories;

use App\Models\BillingReminder;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BillingReminder>
 */
class BillingReminderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'channel' => 'mail',
            'reminder_date' => now()->toDateString(),
            'days_before_due' => $this->faker->randomElement([1, 3, 7]),
            'sent_at' => now(),
        ];
    }
}
