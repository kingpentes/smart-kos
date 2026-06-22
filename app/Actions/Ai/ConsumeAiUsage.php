<?php

namespace App\Actions\Ai;

use App\Enums\AiFeature;
use App\Enums\SubscriptionStatus;
use App\Models\AiUsage;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConsumeAiUsage
{
    /**
     * @param  array<string, mixed>  $metadata
     *
     * @throws ValidationException
     */
    public function handle(User $user, AiFeature $feature, array $metadata = []): AiUsage
    {
        return DB::transaction(function () use ($user, $feature, $metadata): AiUsage {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            if ($lockedUser->ai_trial_credits_remaining > 0) {
                $lockedUser->decrement('ai_trial_credits_remaining');

                return $lockedUser->aiUsages()->create([
                    'feature' => $feature,
                    'source' => 'trial',
                    'metadata' => $metadata,
                ]);
            }

            $subscription = Subscription::query()
                ->whereBelongsTo($lockedUser)
                ->where('role', $lockedUser->role->value)
                ->where('status', SubscriptionStatus::Active->value)
                ->where('starts_at', '<=', now())
                ->where('ends_at', '>', now())
                ->whereColumn('ai_requests_used', '<', 'ai_request_limit')
                ->latest('ends_at')
                ->lockForUpdate()
                ->first();

            if ($subscription === null) {
                throw ValidationException::withMessages([
                    'ai' => 'Kuota AI gratis Anda sudah habis. Aktifkan langganan untuk melanjutkan.',
                ]);
            }

            $subscription->increment('ai_requests_used');

            return $lockedUser->aiUsages()->create([
                'subscription_id' => $subscription->id,
                'feature' => $feature,
                'source' => 'subscription',
                'metadata' => $metadata,
            ]);
        });
    }

    public function refund(AiUsage $usage): void
    {
        DB::transaction(function () use ($usage): void {
            $lockedUsage = AiUsage::query()->lockForUpdate()->find($usage->id);

            if ($lockedUsage === null) {
                return;
            }

            if ($lockedUsage->source === 'trial') {
                User::query()
                    ->whereKey($lockedUsage->user_id)
                    ->lockForUpdate()
                    ->increment('ai_trial_credits_remaining');
            } elseif ($lockedUsage->subscription_id !== null) {
                $subscription = Subscription::query()
                    ->lockForUpdate()
                    ->find($lockedUsage->subscription_id);

                if ($subscription !== null && $subscription->ai_requests_used > 0) {
                    $subscription->decrement('ai_requests_used');
                }
            }

            $lockedUsage->delete();
        });
    }
}
