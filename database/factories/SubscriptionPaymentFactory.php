<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\SubscriptionPayment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SubscriptionPayment>
 */
class SubscriptionPaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->tenant(),
            'subscription_id' => null,
            'plan_code' => 'ai_premium',
            'role' => 'tenant',
            'provider' => 'midtrans',
            'provider_reference' => 'subscription-'.Str::lower(Str::random(20)),
            'method' => 'snap',
            'amount' => 15000,
            'status' => PaymentStatus::Pending,
            'paid_at' => null,
            'raw_payload' => null,
        ];
    }
}
