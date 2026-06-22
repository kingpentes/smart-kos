<?php

namespace Tests\Feature\Subscriptions;

use App\Enums\PaymentStatus;
use App\Models\SubscriptionPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SubscriptionPaymentTest extends TestCase
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

    public function test_tenant_sees_tenant_subscription_plan(): void
    {
        $tenant = User::factory()->tenant()->create();

        $this->actingAs($tenant)
            ->get(route('subscriptions.index'))
            ->assertOk()
            ->assertSee('Fitur AI Premium')
            ->assertDontSee('Boost/Iklan Premium')
            ->assertSee('5 <span class="text-lg font-bold text-indigo-400">Trial</span>', false);
    }

    public function test_owner_can_create_midtrans_subscription_payment(): void
    {
        $owner = User::factory()->owner()->create();

        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'token' => 'owner-snap-token',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v4/redirection/owner-snap-token',
            ]),
        ]);

        $response = $this->actingAs($owner)->post(
            route('subscriptions.payments.store', 'boost_premium'),
        );

        $response->assertRedirect('https://app.sandbox.midtrans.com/snap/v4/redirection/owner-snap-token');
        $this->assertDatabaseHas('subscription_payments', [
            'user_id' => $owner->id,
            'plan_code' => 'boost_premium',
            'role' => 'owner',
            'amount' => 100000,
            'status' => PaymentStatus::Pending->value,
        ]);

        Http::assertSent(fn ($request): bool => $request['transaction_details']['gross_amount'] === 100000
            && $request['item_details'][0]['name'] === 'Boost/Iklan Premium'
            && $request['callbacks']['finish'] === route('subscriptions.payments.finish'));
    }

    public function test_paid_subscription_webhook_activates_owner_plan_idempotently(): void
    {
        $owner = User::factory()->owner()->create();
        $payment = SubscriptionPayment::factory()->for($owner)->create([
            'plan_code' => 'boost_premium',
            'role' => 'owner',
            'amount' => 100000,
            'provider_reference' => 'subscription-owner-webhook',
        ]);
        $payload = $this->midtransPayload($payment, 'settlement');

        $this->postJson(route('webhooks.midtrans'), $payload)
            ->assertOk()
            ->assertJson([
                'payment_id' => $payment->id,
                'payment_type' => 'subscription',
                'status' => PaymentStatus::Paid->value,
            ]);

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $owner->id,
            'plan_code' => 'boost_premium',
            'ai_request_limit' => 0,
        ]);

        $this->postJson(route('webhooks.midtrans'), $payload)->assertOk();

        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertNotNull($payment->refresh()->subscription_id);
    }

    public function test_user_cannot_purchase_plan_for_another_role(): void
    {
        $tenant = User::factory()->tenant()->create();

        $this->actingAs($tenant)
            ->post(route('subscriptions.payments.store', 'boost_premium'))
            ->assertSessionHasErrors('plan');
    }

    /**
     * @return array<string, string>
     */
    private function midtransPayload(SubscriptionPayment $payment, string $transactionStatus): array
    {
        $payload = [
            'order_id' => $payment->provider_reference,
            'status_code' => '200',
            'gross_amount' => number_format($payment->amount, 2, '.', ''),
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
