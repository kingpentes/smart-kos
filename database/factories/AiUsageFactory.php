<?php

namespace Database\Factories;

use App\Enums\AiFeature;
use App\Models\AiUsage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiUsage>
 */
class AiUsageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'subscription_id' => null,
            'feature' => AiFeature::BoardingHouseSearch,
            'source' => 'trial',
            'metadata' => null,
        ];
    }
}
