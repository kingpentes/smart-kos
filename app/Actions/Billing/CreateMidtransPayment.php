<?php

namespace App\Actions\Billing;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\Payments\MidtransGateway;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateMidtransPayment
{
    public function __construct(private MidtransGateway $midtransGateway) {}

    /**
     * @throws ValidationException
     */
    public function handle(Invoice $invoice): Payment
    {
        $invoice->loadMissing(['lease.tenant', 'lease.boardingHouse']);

        if ($invoice->status === InvoiceStatus::Paid) {
            throw ValidationException::withMessages([
                'invoice' => 'Tagihan ini sudah lunas.',
            ]);
        }

        if ($invoice->status === InvoiceStatus::Cancelled) {
            throw ValidationException::withMessages([
                'invoice' => 'Tagihan ini sudah dibatalkan.',
            ]);
        }

        $pendingPayment = $invoice->payments()
            ->where('provider', 'midtrans')
            ->where('status', PaymentStatus::Pending->value)
            ->latest()
            ->first();

        if ($pendingPayment !== null && filled($pendingPayment->raw_payload['redirect_url'] ?? null)) {
            return $pendingPayment;
        }

        $orderId = $this->makeOrderId($invoice);
        $snapTransaction = $this->midtransGateway->createSnapTransaction($invoice, $orderId);

        return $invoice->payments()->create([
            'provider' => 'midtrans',
            'provider_reference' => $orderId,
            'method' => 'snap',
            'amount' => $invoice->amount,
            'status' => PaymentStatus::Pending,
            'raw_payload' => [
                'token' => $snapTransaction['token'],
                'redirect_url' => $snapTransaction['redirect_url'],
            ],
        ]);
    }

    private function makeOrderId(Invoice $invoice): string
    {
        return Str::limit(
            Str::slug($invoice->number).'-'.$invoice->id.'-'.now()->format('YmdHis'),
            50,
            '',
        );
    }
}
