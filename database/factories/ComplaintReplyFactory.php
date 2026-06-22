<?php

namespace Database\Factories;

use App\Models\Complaint;
use App\Models\ComplaintReply;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComplaintReply>
 */
class ComplaintReplyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'complaint_id' => Complaint::factory(),
            'user_id' => User::factory(),
            'message' => $this->faker->sentence(),
        ];
    }
}
