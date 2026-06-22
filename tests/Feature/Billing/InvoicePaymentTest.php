<?php

namespace Tests\Feature\Billing;

use App\Enums\BookingStatus;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Enums\RoomStatus;
use App\Models\BoardingHouse;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_accepting_booking_creates_initial_unpaid_invoice(): void
    {
        $owner = User::factory()->owner()->create();
        $tenant = User::factory()->tenant()->create();
        $boardingHouse = BoardingHouse::factory()->published()->for($owner, 'owner')->create();
        $room = Room::factory()->for($boardingHouse)->create([
            'price_monthly' => 1250000,
            'status' => RoomStatus::Available,
        ]);
        $booking = Booking::factory()->for($boardingHouse)->for($room)->for($tenant, 'tenant')->create([
            'start_date' => now()->addDays(7)->toDateString(),
            'duration_months' => 3,
            'status' => BookingStatus::Pending,
        ]);

        $response = $this->actingAs($owner)->patch(route('owner.bookings.accept', $booking));

        $response->assertRedirect(route('owner.bookings.index'));
        $this->assertDatabaseHas('invoices', [
            'amount' => 1250000,
            'status' => InvoiceStatus::Unpaid->value,
        ]);

        $invoice = Invoice::query()->firstOrFail();

        $this->assertSame($booking->refresh()->lease->id, $invoice->lease_id);
        $this->assertSame($booking->start_date->toDateString(), $invoice->due_date->toDateString());
    }

    public function test_tenant_can_view_own_invoice(): void
    {
        [$tenant, $invoice] = $this->createAcceptedBookingWithInvoice();

        $response = $this->actingAs($tenant)->get(route('tenant.invoices.show', $invoice));

        $response->assertOk();
        $response->assertSee($invoice->number);
    }

    public function test_tenant_cannot_view_another_tenant_invoice(): void
    {
        [, $invoice] = $this->createAcceptedBookingWithInvoice();
        $otherTenant = User::factory()->tenant()->create();

        $response = $this->actingAs($otherTenant)->get(route('tenant.invoices.show', $invoice));

        $response->assertForbidden();
    }

    public function test_owner_can_mark_invoice_paid_manually(): void
    {
        [, $invoice, $owner] = $this->createAcceptedBookingWithInvoice();

        $response = $this->actingAs($owner)->patch(route('owner.invoices.mark-paid', $invoice));

        $response->assertRedirect(route('owner.invoices.index'));
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => InvoiceStatus::Paid->value,
        ]);
        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'provider' => 'manual',
            'method' => 'manual',
            'amount' => $invoice->amount,
            'status' => PaymentStatus::Paid->value,
        ]);
    }

    public function test_owner_cannot_mark_another_owner_invoice_paid(): void
    {
        [, $invoice] = $this->createAcceptedBookingWithInvoice();
        $otherOwner = User::factory()->owner()->create();
        \App\Models\Subscription::factory()->for($otherOwner)->create([
            'role' => \App\Enums\UserRole::Owner->value,
            'status' => \App\Enums\SubscriptionStatus::Active->value,
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
        ]);

        $response = $this->actingAs($otherOwner)->patch(route('owner.invoices.mark-paid', $invoice));

        $response->assertForbidden();
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => InvoiceStatus::Unpaid->value,
        ]);
        $this->assertDatabaseCount('payments', 0);
    }

    /**
     * @return array{0: User, 1: Invoice, 2: User}
     */
    private function createAcceptedBookingWithInvoice(): array
    {
        $owner = User::factory()->owner()->create();
        \App\Models\Subscription::factory()->for($owner)->create([
            'role' => \App\Enums\UserRole::Owner->value,
            'status' => \App\Enums\SubscriptionStatus::Active->value,
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
        ]);
        $tenant = User::factory()->tenant()->create();
        $boardingHouse = BoardingHouse::factory()->published()->for($owner, 'owner')->create();
        $room = Room::factory()->for($boardingHouse)->create([
            'price_monthly' => 900000,
            'status' => RoomStatus::Available,
        ]);
        $booking = Booking::factory()->for($boardingHouse)->for($room)->for($tenant, 'tenant')->create([
            'status' => BookingStatus::Pending,
        ]);

        $this->actingAs($owner)->patch(route('owner.bookings.accept', $booking));

        return [$tenant, Invoice::query()->firstOrFail(), $owner];
    }
}
