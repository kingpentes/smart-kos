<?php

namespace App\Actions\Subscriptions;

use App\Enums\PaymentStatus;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Services\Payments\MidtransGateway;
use Illuminate\Support\Str;

class CreateSubscriptionPayment
{
    public function __construct(private MidtransGateway $midtransGateway) {}

    /**
     * @param  array{name: string, price: int, duration_days: int, ai_request_limit: int, features: array<int, string>}  $plan
     */
    public function handle(User $user, string $planCode, array $plan): SubscriptionPayment
    {
        $pendingPayment = $user->subscriptionPayments()
            ->where('plan_code', $planCode)
            ->where('provider', 'midtrans')
            ->where('status', PaymentStatus::Pending->value)
            ->latest()
            ->first();

        if ($pendingPayment !== null && filled($pendingPayment->raw_payload['redirect_url'] ?? null)) {
            return $pendingPayment;
        }

        $orderId = Str::limit(
            'subscription-'.$user->id.'-'.$planCode.'-'.now()->format('YmdHis'),
            50,
            '',
        );
        $snapTransaction = $this->midtransGateway->createSubscriptionTransaction($user, $plan, $orderId);

        return $user->subscriptionPayments()->create([
            'plan_code' => $planCode,
            'role' => $user->role->value,
            'provider' => 'midtrans',
            'provider_reference' => $orderId,
            'method' => 'snap',
            'amount' => $plan['price'],
            'status' => PaymentStatus::Pending,
            'raw_payload' => [
                'token' => $snapTransaction['token'],
                'redirect_url' => $snapTransaction['redirect_url'],
            ],
        ]);
    }
}
