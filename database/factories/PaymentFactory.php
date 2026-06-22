<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
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
            'provider' => 'manual',
            'provider_reference' => null,
            'method' => 'manual',
            'amount' => $this->faker->numberBetween(700000, 2500000),
            'status' => PaymentStatus::Paid,
            'paid_at' => now(),
            'raw_payload' => null,
        ];
    }
}
