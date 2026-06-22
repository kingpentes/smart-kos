<?php

namespace App\Actions\Subscriptions;

use App\Enums\PaymentStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Services\Payments\MidtransGateway;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateSubscriptionPaymentStatus
{
    public function __construct(private MidtransGateway $midtransGateway) {}

    /**
     * @param  array<string, mixed>  $payload
     *
     * @throws ValidationException
     */
    public function handle(array $payload, string $payloadKey = 'notification'): SubscriptionPayment
    {
        if (! $this->midtransGateway->hasValidSignature($payload)) {
            throw ValidationException::withMessages([
                'signature_key' => 'Signature Midtrans tidak valid.',
            ]);
        }

        return DB::transaction(function () use ($payload, $payloadKey): SubscriptionPayment {
            $payment = SubscriptionPayment::query()
                ->where('provider', 'midtrans')
                ->where('provider_reference', (string) $payload['order_id'])
                ->with('subscription')
                ->lockForUpdate()
                ->firstOrFail();

            $grossAmount = (int) round((float) ($payload['gross_amount'] ?? 0));

            if ($grossAmount !== $payment->amount) {
                throw ValidationException::withMessages([
                    'gross_amount' => 'Nominal pembayaran langganan tidak sesuai.',
                ]);
            }

            $status = $this->midtransGateway->paymentStatusFromPayload($payload);

            $payment->update([
                'method' => (string) ($payload['payment_type'] ?? $payment->method),
                'status' => $status,
                'paid_at' => $status === PaymentStatus::Paid && $payment->paid_at === null ? now() : $payment->paid_at,
                'raw_payload' => [
                    ...($payment->raw_payload ?? []),
                    $payloadKey => $payload,
                ],
            ]);

            if ($status === PaymentStatus::Paid && $payment->subscription_id === null) {
                $subscription = $this->activateSubscription($payment);
                $payment->update(['subscription_id' => $subscription->id]);
            }

            return $payment->refresh()->load('subscription');
        });
    }

    private function activateSubscription(SubscriptionPayment $payment): Subscription
    {
        /** @var array{name: string, price: int, duration_days: int, ai_request_limit: int, features: array<int, string>}|null $plan */
        $plan = config("subscriptions.plans.{$payment->role}.{$payment->plan_code}");

        if ($plan === null) {
            throw ValidationException::withMessages([
                'plan_code' => 'Paket langganan tidak lagi tersedia.',
            ]);
        }

        $user = User::query()->lockForUpdate()->findOrFail($payment->user_id);
        $activeSubscriptions = Subscription::query()
            ->whereBelongsTo($user)
            ->where('role', $payment->role)
            ->where('status', SubscriptionStatus::Active->value)
            ->where('ends_at', '>', now())
            ->get();

        $samePlan = $activeSubscriptions->where('plan_code', $payment->plan_code)->first();

        if ($activeSubscriptions->isNotEmpty() && ! $samePlan) {
            // Upgrade or change plan: Cancel existing subscriptions
            Subscription::query()
                ->whereBelongsTo($user)
                ->where('role', $payment->role)
                ->where('status', SubscriptionStatus::Active->value)
                ->where('ends_at', '>', now())
                ->update(['ends_at' => now(), 'status' => SubscriptionStatus::Cancelled]);
            
            $startsAt = now();
        } else {
            // New subscription or extending same plan
            $latestEnd = $activeSubscriptions->where('plan_code', $payment->plan_code)->max('ends_at');
            $startsAt = $latestEnd === null ? now() : Carbon::parse($latestEnd);
        }

        return $user->subscriptions()->create([
            'plan_code' => $payment->plan_code,
            'role' => $payment->role,
            'name' => $plan['name'],
            'amount' => $plan['price'],
            'ai_request_limit' => $plan['ai_request_limit'] ?? 0,
            'ai_requests_used' => 0,
            'status' => SubscriptionStatus::Active,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addDays($plan['duration_days']),
        ]);
    }
}
