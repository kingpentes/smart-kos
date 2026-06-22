<?php

namespace App\Console\Commands\Billing;

use App\Actions\Billing\MarkOverdueInvoices;
use App\Actions\Billing\SendDueInvoiceReminders;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('billing:run-smart-billing {--date= : Run date in YYYY-MM-DD format}')]
#[Description('Send due invoice reminders and mark overdue invoices.')]
class RunSmartBilling extends Command
{
    public function handle(
        SendDueInvoiceReminders $sendDueInvoiceReminders,
        MarkOverdueInvoices $markOverdueInvoices
    ): int {
        $runDate = $this->option('date')
            ? CarbonImmutable::parse($this->option('date'))->startOfDay()
            : CarbonImmutable::today();

        $remindersSent = $sendDueInvoiceReminders->handle($runDate);
        $overdueInvoices = $markOverdueInvoices->handle($runDate);

        $this->info("Smart billing completed: {$remindersSent} reminder(s) sent, {$overdueInvoices} invoice(s) marked overdue.");

        return self::SUCCESS;
    }
}
