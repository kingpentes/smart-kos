<?php

namespace Tests\Feature\Billing;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\BoardingHouse;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MidtransPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.midtrans.server_key' => 'test-server-key',
            'services.midtrans.client_key' => 'test-client-key',
            'services.midtrans.is_production' => false,
        ]);
    }

    public function test_tenant_can_create_midtrans_payment_for_unpaid_invoice(): void
    {
        [$tenant, $invoice] = $this->createInvoiceForTenant();

        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'token' => 'snap-token',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v4/redirection/snap-token',
            ]),
        ]);

        $response = $this->actingAs($tenant)->post(route('tenant.payments.midtrans.store', $invoice));

        $response->assertRedirect('https://app.sandbox.midtrans.com/snap/v4/redirection/snap-token');
        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'provider' => 'midtrans',
            'method' => 'snap',
            'amount' => $invoice->amount,
            'status' => PaymentStatus::Pending->value,
        ]);

        Http::assertSent(fn ($request): bool => $request->hasHeader(
            'Authorization',
            'Basic '.base64_encode('test-server-key:'),
        )
            && $request->url() === 'https://app.sandbox.midtrans.com/snap/v1/transactions'
            && $request['transaction_details']['gross_amount'] === $invoice->amount
            && $request['callbacks']['finish'] === route('tenant.payments.midtrans.finish'));
    }

    public function test_midtrans_finish_redirect_verifies_payment_status(): void
    {
        [$tenant, $invoice] = $this->createInvoiceForTenant();
        $payment = Payment::factory()->for($invoice)->create([
            'provider' => 'midtrans',
            'provider_reference' => 'midtrans-order-return',
            'method' => 'snap',
            'amount' => $invoice->amount,
            'status' => PaymentStatus::Pending,
            'paid_at' => null,
            'raw_payload' => [
                'token' => 'snap-token',
                'redirect_url' => 'https://example.com/pay',
            ],
        ]);
        $payload = $this->midtransPayload(
            orderId: $payment->provider_reference,
            grossAmount: number_format($invoice->amount, 2, '.', ''),
            transactionStatus: 'settlement',
        );

        Http::fake([
            'https://api.sandbox.midtrans.com/v2/midtrans-order-return/status' => Http::response([
                ...$payload,
                'payment_type' => 'bank_transfer',
            ]),
        ]);

        $response = $this->actingAs($tenant)->get(route('tenant.payments.midtrans.finish', [
            'order_id' => $payment->provider_reference,
            'status_code' => '200',
            'transaction_status' => 'settlement',
        ]));

        $response
            ->assertOk()
            ->assertViewIs('tenant.payments.success')
            ->assertViewHas('payment')
            ->assertViewHas('invoice');

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => PaymentStatus::Paid->value,
            'method' => 'bank_transfer',
        ]);
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => InvoiceStatus::Paid->value,
        ]);

        Http::assertSent(fn ($request): bool => $request->hasHeader(
            'Authorization',
            'Basic '.base64_encode('test-server-key:'),
        )
            && $request->url() === 'https://api.sandbox.midtrans.com/v2/midtrans-order-return/status');
    }

    public function test_midtrans_webhook_marks_payment_and_invoice_paid(): void
    {
        [, $invoice] = $this->createInvoiceForTenant();
        $payment = Payment::factory()->for($invoice)->create([
            'provider' => 'midtrans',
            'provider_reference' => 'midtrans-order-1',
            'method' => 'snap',
            'amount' => $invoice->amount,
            'status' => PaymentStatus::Pending,
            'paid_at' => null,
            'raw_payload' => [
                'token' => 'snap-token',
                'redirect_url' => 'https://example.com/pay',
            ],
        ]);
        $payload = $this->midtransPayload(
            orderId: $payment->provider_reference,
            grossAmount: number_format($invoice->amount, 2, '.', ''),
            transactionStatus: 'settlement',
        );

        $response = $this->postJson(route('webhooks.midtrans'), $payload);

        $response
            ->assertOk()
            ->assertJson([
                'payment_id' => $payment->id,
                'status' => PaymentStatus::Paid->value,
            ]);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => PaymentStatus::Paid->value,
            'method' => 'bank_transfer',
        ]);
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => InvoiceStatus::Paid->value,
        ]);
    }

    public function test_midtrans_webhook_rejects_invalid_signature(): void
    {
        [, $invoice] = $this->createInvoiceForTenant();
        $payment = Payment::factory()->for($invoice)->create([
            'provider' => 'midtrans',
            'provider_reference' => 'midtrans-order-invalid',
            'status' => PaymentStatus::Pending,
        ]);
        $payload = $this->midtransPayload(
            orderId: $payment->provider_reference,
            grossAmount: number_format($invoice->amount, 2, '.', ''),
            transactionStatus: 'settlement',
        );
        $payload['signature_key'] = 'invalid-signature';

        $this->postJson(route('webhooks.midtrans'), $payload)
            ->assertUnprocessable()
            ->assertJsonPath('errors.signature_key.0', 'Signature Midtrans tidak valid.');

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => PaymentStatus::Pending->value,
        ]);
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => InvoiceStatus::Unpaid->value,
        ]);
    }

    public function test_midtrans_paid_webhook_is_idempotent(): void
    {
        [, $invoice] = $this->createInvoiceForTenant();
        $payment = Payment::factory()->for($invoice)->create([
            'provider' => 'midtrans',
            'provider_reference' => 'midtrans-order-repeat',
            'amount' => $invoice->amount,
            'status' => PaymentStatus::Pending,
            'paid_at' => null,
        ]);
        $payload = $this->midtransPayload(
            orderId: $payment->provider_reference,
            grossAmount: number_format($invoice->amount, 2, '.', ''),
            transactionStatus: 'settlement',
        );

        $this->postJson(route('webhooks.midtrans'), $payload)->assertOk();
        $paidAt = $payment->refresh()->paid_at;

        $this->postJson(route('webhooks.midtrans'), $payload)->assertOk();

        $this->assertDatabaseCount('payments', 1);
        $this->assertTrue($paidAt->equalTo($payment->refresh()->paid_at));
        $this->assertSame(InvoiceStatus::Paid, $invoice->refresh()->status);
    }

    /**
     * @return array{0: User, 1: Invoice}
     */
    private function createInvoiceForTenant(): array
    {
        $tenant = User::factory()->tenant()->create();
        $owner = User::factory()->owner()->create();
        $boardingHouse = BoardingHouse::factory()->published()->for($owner, 'owner')->create();
        $room = Room::factory()->for($boardingHouse)->create();
        $lease = Lease::factory()->create([
            'boarding_house_id' => $boardingHouse->id,
            'room_id' => $room->id,
            'tenant_id' => $tenant->id,
            'owner_id' => $owner->id,
        ]);
        $invoice = Invoice::factory()->for($lease)->create([
            'amount' => 900000,
            'status' => InvoiceStatus::Unpaid,
        ]);

        return [$tenant, $invoice];
    }

    /**
     * @return array<string, string>
     */
    private function midtransPayload(string $orderId, string $grossAmount, string $transactionStatus): array
    {
        $payload = [
            'order_id' => $orderId,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'transaction_status' => $transactionStatus,
            'fraud_status' => 'accept',
            'payment_type' => 'bank_transfer',
        ];

        $payload['signature_key'] = hash(
            'sha512',
            $payload['order_id'].$payload['status_code'].$payload['gross_amount'].'test-server-key',
        );

        return $payload;
    }
}
