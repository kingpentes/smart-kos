<?php

namespace Tests\Feature\Billing;

use App\Enums\InvoiceStatus;
use App\Models\BoardingHouse;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Room;
use App\Models\User;
use App\Notifications\InvoiceDueReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SmartBillingReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_smart_billing_sends_h7_h3_and_h1_reminders_once(): void
    {
        Carbon::setTestNow('2026-06-01 08:00:00');
        Notification::fake();

        $h7 = $this->createInvoiceDueOn('2026-06-08');
        $h3 = $this->createInvoiceDueOn('2026-06-04');
        $h1 = $this->createInvoiceDueOn('2026-06-02');

        $this->artisan('billing:run-smart-billing', ['--date' => '2026-06-01'])
            ->expectsOutput('Smart billing completed: 3 reminder(s) sent, 0 invoice(s) marked overdue.')
            ->assertSuccessful();

        $this->artisan('billing:run-smart-billing', ['--date' => '2026-06-01'])
            ->expectsOutput('Smart billing completed: 0 reminder(s) sent, 0 invoice(s) marked overdue.')
            ->assertSuccessful();

        Notification::assertSentTo($h7->lease->tenant, InvoiceDueReminder::class, fn (InvoiceDueReminder $notification): bool => $notification->daysBeforeDue === 7);
        Notification::assertSentTo($h3->lease->tenant, InvoiceDueReminder::class, fn (InvoiceDueReminder $notification): bool => $notification->daysBeforeDue === 3);
        Notification::assertSentTo($h1->lease->tenant, InvoiceDueReminder::class, fn (InvoiceDueReminder $notification): bool => $notification->daysBeforeDue === 1);

        $this->assertDatabaseCount('billing_reminders', 3);

        Carbon::setTestNow();
    }

    public function test_smart_billing_does_not_remind_paid_invoice(): void
    {
        Notification::fake();

        $invoice = $this->createInvoiceDueOn('2026-06-08', InvoiceStatus::Paid);

        $this->artisan('billing:run-smart-billing', ['--date' => '2026-06-01'])
            ->expectsOutput('Smart billing completed: 0 reminder(s) sent, 0 invoice(s) marked overdue.')
            ->assertSuccessful();

        Notification::assertNothingSent();
        $this->assertDatabaseMissing('billing_reminders', [
            'invoice_id' => $invoice->id,
        ]);
    }

    public function test_smart_billing_marks_unpaid_past_due_invoice_overdue(): void
    {
        $overdueInvoice = $this->createInvoiceDueOn('2026-05-31');
        $todayInvoice = $this->createInvoiceDueOn('2026-06-01');
        $paidPastDueInvoice = $this->createInvoiceDueOn('2026-05-30', InvoiceStatus::Paid);

        $this->artisan('billing:run-smart-billing', ['--date' => '2026-06-01'])
            ->expectsOutput('Smart billing completed: 0 reminder(s) sent, 1 invoice(s) marked overdue.')
            ->assertSuccessful();

        $this->assertDatabaseHas('invoices', [
            'id' => $overdueInvoice->id,
            'status' => InvoiceStatus::Overdue->value,
        ]);
        $this->assertDatabaseHas('invoices', [
            'id' => $todayInvoice->id,
            'status' => InvoiceStatus::Unpaid->value,
        ]);
        $this->assertDatabaseHas('invoices', [
            'id' => $paidPastDueInvoice->id,
            'status' => InvoiceStatus::Paid->value,
        ]);
    }

    private function createInvoiceDueOn(string $dueDate, InvoiceStatus $status = InvoiceStatus::Unpaid): Invoice
    {
        $tenant = User::factory()->tenant()->create();
        $owner = User::factory()->owner()->create();
        $boardingHouse = BoardingHouse::factory()->published()->for($owner, 'owner')->create();
        $room = Room::factory()->for($boardingHouse)->create(['price_monthly' => 1000000]);
        $booking = Booking::factory()->for($boardingHouse)->for($room)->for($tenant, 'tenant')->create();
        $lease = Lease::factory()->for($booking)->for($boardingHouse)->for($room)->for($tenant, 'tenant')->for($owner, 'owner')->create();

        return Invoice::factory()->for($lease)->create([
            'due_date' => $dueDate,
            'amount' => 1000000,
            'status' => $status,
        ]);
    }
}
