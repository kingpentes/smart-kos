<?php

namespace App\Actions\Billing;

use App\Enums\InvoiceStatus;
use App\Models\BillingReminder;
use App\Models\Invoice;
use App\Notifications\InvoiceDueReminder;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class SendDueInvoiceReminders
{
    /**
     * @param  array<int, int>  $daysBeforeDue
     */
    public function handle(?CarbonImmutable $runDate = null, array $daysBeforeDue = [7, 3, 1]): int
    {
        $runDate ??= CarbonImmutable::today();
        $sentCount = 0;

        foreach ($daysBeforeDue as $days) {
            $dueDate = $runDate->addDays($days);

            Invoice::query()
                ->where('status', InvoiceStatus::Unpaid->value)
                ->whereDate('due_date', $dueDate)
                ->with(['lease.tenant', 'lease.boardingHouse'])
                ->chunkById(50, function ($invoices) use ($runDate, $days, &$sentCount): void {
                    foreach ($invoices as $invoice) {
                        if ($this->sendOnce($invoice, $runDate, $days)) {
                            $sentCount++;
                        }
                    }
                });
        }

        return $sentCount;
    }

    private function sendOnce(Invoice $invoice, CarbonImmutable $runDate, int $daysBeforeDue): bool
    {
        return DB::transaction(function () use ($invoice, $runDate, $daysBeforeDue): bool {
            $alreadySent = BillingReminder::query()
                ->where('invoice_id', $invoice->id)
                ->where('channel', 'mail')
                ->whereDate('reminder_date', $runDate)
                ->where('days_before_due', $daysBeforeDue)
                ->lockForUpdate()
                ->exists();

            if ($alreadySent) {
                return false;
            }

            $invoice->lease->tenant->notify(new InvoiceDueReminder($invoice, $daysBeforeDue));

            BillingReminder::query()->create([
                'invoice_id' => $invoice->id,
                'channel' => 'mail',
                'reminder_date' => $runDate,
                'days_before_due' => $daysBeforeDue,
                'sent_at' => now(),
            ]);

            return true;
        });
    }
}
