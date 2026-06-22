<?php

namespace App\Actions\Billing;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Services\Payments\MidtransGateway;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateMidtransPaymentStatus
{
    public function __construct(private MidtransGateway $midtransGateway) {}

    /**
     * @param  array<string, mixed>  $payload
     *
     * @throws ValidationException
     */
    public function handle(array $payload, string $payloadKey = 'notification'): Payment
    {
        if (! $this->midtransGateway->hasValidSignature($payload)) {
            throw ValidationException::withMessages([
                'signature_key' => 'Signature Midtrans tidak valid.',
            ]);
        }

        return DB::transaction(function () use ($payload, $payloadKey): Payment {
            $payment = Payment::query()
                ->where('provider', 'midtrans')
                ->where('provider_reference', (string) $payload['order_id'])
                ->with('invoice')
                ->lockForUpdate()
                ->firstOrFail();

            $invoice = $payment->invoice()->lockForUpdate()->firstOrFail();
            $status = $this->midtransGateway->paymentStatusFromPayload($payload);

            $payment->update([
                'method' => (string) ($payload['payment_type'] ?? $payment->method),
                'amount' => (int) round((float) ($payload['gross_amount'] ?? $payment->amount)),
                'status' => $status,
                'paid_at' => $status === PaymentStatus::Paid && $payment->paid_at === null ? now() : $payment->paid_at,
                'raw_payload' => [
                    ...($payment->raw_payload ?? []),
                    $payloadKey => $payload,
                ],
            ]);

            if ($status === PaymentStatus::Paid && $invoice->status !== InvoiceStatus::Paid) {
                $invoice->update([
                    'status' => InvoiceStatus::Paid,
                ]);
            }

            return $payment;
        });
    }
}
