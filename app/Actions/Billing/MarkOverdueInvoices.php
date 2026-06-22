<?php

namespace App\Actions\Billing;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Carbon\CarbonImmutable;

class MarkOverdueInvoices
{
    public function handle(?CarbonImmutable $runDate = null): int
    {
        $runDate ??= CarbonImmutable::today();

        return Invoice::query()
            ->where('status', InvoiceStatus::Unpaid->value)
            ->whereDate('due_date', '<', $runDate)
            ->update([
                'status' => InvoiceStatus::Overdue,
            ]);
    }
}
