<?php

namespace App\Actions\Billing;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Lease;
use Illuminate\Support\Facades\DB;

class CreateInitialInvoice
{
    public function handle(Lease $lease): Invoice
    {
        return DB::transaction(function () use ($lease): Invoice {
            $periodStart = $lease->start_date->copy();
            $periodEnd = $periodStart->copy()->addMonthNoOverflow()->subDay();

            $existingInvoice = $lease->invoices()
                ->whereDate('period_start', $periodStart)
                ->whereDate('period_end', $periodEnd)
                ->first();

            if ($existingInvoice) {
                return $existingInvoice;
            }

            return $lease->invoices()->create([
                'number' => $this->nextNumber(),
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'due_date' => $lease->start_date,
                'amount' => $lease->room->price_monthly,
                'status' => InvoiceStatus::Unpaid,
            ]);
        });
    }

    private function nextNumber(): string
    {
        $prefix = 'INV-'.now()->format('Ym').'-';
        $lastInvoice = Invoice::query()
            ->where('number', 'like', "{$prefix}%")
            ->lockForUpdate()
            ->latest('id')
            ->first();

        $nextSequence = $lastInvoice
            ? ((int) str($lastInvoice->number)->afterLast('-')->toString()) + 1
            : 1;

        return $prefix.str_pad((string) $nextSequence, 5, '0', STR_PAD_LEFT);
    }
}
