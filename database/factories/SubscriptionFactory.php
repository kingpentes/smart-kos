<?php

namespace Database\Factories;

use App\Enums\SubscriptionStatus;
use App\Enums\UserRole;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
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
            'plan_code' => 'ai_premium',
            'role' => UserRole::Tenant->value,
            'name' => 'Fitur AI Premium',
            'amount' => 15000,
            'ai_request_limit' => 999999,
            'ai_requests_used' => 0,
            'status' => SubscriptionStatus::Active,
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
        ];
    }

    public function owner(): static
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => User::factory()->owner(),
            'plan_code' => 'boost_premium',
            'role' => UserRole::Owner->value,
            'name' => 'Boost/Iklan Premium',
            'amount' => 100000,
            'ai_request_limit' => 0,
        ]);
    }
}
