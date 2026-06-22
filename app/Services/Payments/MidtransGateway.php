<?php

namespace App\Services\Payments;

use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class MidtransGateway
{
    /**
     * @return array{token: string, redirect_url: string}
     */
    public function createSnapTransaction(Invoice $invoice, string $orderId): array
    {
        $invoice->loadMissing(['lease.tenant', 'lease.boardingHouse']);
        $tenant = $invoice->lease->tenant;
        $boardingHouse = $invoice->lease->boardingHouse;

        $response = Http::withBasicAuth($this->serverKey(), '')
            ->acceptJson()
            ->asJson()
            ->timeout(10)
            ->connectTimeout(5)
            ->post($this->snapEndpoint(), [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $invoice->amount,
                ],
                'customer_details' => [
                    'first_name' => $tenant->name,
                    'email' => $tenant->email,
                    'phone' => $tenant->phone,
                ],
                'item_details' => [
                    [
                        'id' => $invoice->number,
                        'price' => $invoice->amount,
                        'quantity' => 1,
                        'name' => 'Tagihan '.$boardingHouse->name,
                    ],
                ],
                'callbacks' => [
                    'finish' => route('tenant.payments.midtrans.finish'),
                ],
            ])
            ->throw();

        /** @var array{token: string, redirect_url: string} $payload */
        $payload = $response->json();

        return $payload;
    }

    /**
     * @param  array{name: string, price: int, duration_days: int, ai_request_limit: int, features: array<int, string>}  $plan
     * @return array{token: string, redirect_url: string}
     */
    public function createSubscriptionTransaction(User $user, array $plan, string $orderId): array
    {
        $response = Http::withBasicAuth($this->serverKey(), '')
            ->acceptJson()
            ->asJson()
            ->timeout(10)
            ->connectTimeout(5)
            ->post($this->snapEndpoint(), [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $plan['price'],
                ],
                'customer_details' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                ],
                'item_details' => [
                    [
                        'id' => $orderId,
                        'price' => $plan['price'],
                        'quantity' => 1,
                        'name' => $plan['name'],
                    ],
                ],
                'callbacks' => [
                    'finish' => route('subscriptions.payments.finish'),
                ],
            ])
            ->throw();

        /** @var array{token: string, redirect_url: string} $payload */
        $payload = $response->json();

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function getTransactionStatus(string $orderId): array
    {
        /** @var array<string, mixed> $payload */
        $payload = Http::withBasicAuth($this->serverKey(), '')
            ->acceptJson()
            ->timeout(10)
            ->connectTimeout(5)
            ->get($this->statusEndpoint($orderId))
            ->throw()
            ->json();

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function hasValidSignature(array $payload): bool
    {
        $expectedSignature = hash('sha512', implode('', [
            (string) ($payload['order_id'] ?? ''),
            (string) ($payload['status_code'] ?? ''),
            (string) ($payload['gross_amount'] ?? ''),
            $this->serverKey(),
        ]));

        return hash_equals($expectedSignature, (string) ($payload['signature_key'] ?? ''));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function paymentStatusFromPayload(array $payload): PaymentStatus
    {
        $transactionStatus = (string) ($payload['transaction_status'] ?? '');
        $fraudStatus = (string) ($payload['fraud_status'] ?? '');

        if ($transactionStatus === 'capture') {
            return $fraudStatus === 'challenge' ? PaymentStatus::Pending : PaymentStatus::Paid;
        }

        return match ($transactionStatus) {
            'settlement' => PaymentStatus::Paid,
            'pending' => PaymentStatus::Pending,
            'expire' => PaymentStatus::Expired,
            'deny', 'cancel', 'failure' => PaymentStatus::Failed,
            default => PaymentStatus::Pending,
        };
    }

    private function snapEndpoint(): string
    {
        if ((bool) config('services.midtrans.is_production')) {
            return 'https://app.midtrans.com/snap/v1/transactions';
        }

        return 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    private function statusEndpoint(string $orderId): string
    {
        return $this->apiBaseUrl().'/v2/'.rawurlencode($orderId).'/status';
    }

    private function apiBaseUrl(): string
    {
        if ((bool) config('services.midtrans.is_production')) {
            return 'https://api.midtrans.com';
        }

        return 'https://api.sandbox.midtrans.com';
    }

    private function serverKey(): string
    {
        return (string) config('services.midtrans.server_key');
    }
}
