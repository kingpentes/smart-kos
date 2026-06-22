<?php

namespace App\Actions\Billing;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MarkInvoicePaid
{
    /**
     * @throws ValidationException
     */
    public function handle(Invoice $invoice, string $method = 'manual'): Payment
    {
        return DB::transaction(function () use ($invoice, $method): Payment {
            $invoice = Invoice::query()
                ->lockForUpdate()
                ->findOrFail($invoice->id);

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

            $payment = $invoice->payments()->create([
                'provider' => 'manual',
                'provider_reference' => 'MANUAL-'.$invoice->number,
                'method' => $method,
                'amount' => $invoice->amount,
                'status' => PaymentStatus::Paid,
                'paid_at' => now(),
                'raw_payload' => [
                    'source' => 'owner_manual_confirmation',
                ],
            ]);

            $invoice->update([
                'status' => InvoiceStatus::Paid,
            ]);

            return $payment;
        });
    }
}
