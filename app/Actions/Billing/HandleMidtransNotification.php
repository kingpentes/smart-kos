<?php

namespace App\Actions\Billing;

use App\Actions\Subscriptions\UpdateSubscriptionPaymentStatus;
use App\Models\Payment;
use App\Models\SubscriptionPayment;
use Illuminate\Validation\ValidationException;

class HandleMidtransNotification
{
    public function __construct(
        private UpdateMidtransPaymentStatus $updateMidtransPaymentStatus,
        private UpdateSubscriptionPaymentStatus $updateSubscriptionPaymentStatus,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     *
     * @throws ValidationException
     */
    public function handle(array $payload): Payment|SubscriptionPayment
    {
        $isSubscriptionPayment = SubscriptionPayment::query()
            ->where('provider', 'midtrans')
            ->where('provider_reference', (string) ($payload['order_id'] ?? ''))
            ->exists();

        if ($isSubscriptionPayment) {
            return $this->updateSubscriptionPaymentStatus->handle($payload);
        }

        return $this->updateMidtransPaymentStatus->handle($payload);
    }
}
