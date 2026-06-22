<?php

namespace App\Services\Ai;

use App\Models\User;

class AiAccessService
{
    /**
     * @return array{
     *     trial_credits: int,
     *     has_subscription: bool,
     *     subscription_name: string|null,
     *     subscription_ends_at: string|null,
     *     subscription_remaining: int,
     *     total_remaining: int
     * }
     */
    public function status(User $user): array
    {
        $subscription = $user->activeSubscription();
        $subscriptionRemaining = $subscription?->remainingAiRequests() ?? 0;

        return [
            'trial_credits' => $user->ai_trial_credits_remaining,
            'has_subscription' => $subscription !== null,
            'subscription_name' => $subscription?->name,
            'subscription_ends_at' => $subscription?->ends_at->toIso8601String(),
            'subscription_remaining' => $subscriptionRemaining,
            'total_remaining' => $subscriptionRemaining === -1 
                ? -1 
                : $user->ai_trial_credits_remaining + $subscriptionRemaining,
        ];
    }
}
