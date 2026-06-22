<?php

namespace Tests\Feature\Reports;

use App\Enums\AiFeature;
use App\Enums\BookingStatus;
use App\Enums\InvoiceStatus;
use App\Enums\LeaseStatus;
use App\Enums\PaymentStatus;
use App\Enums\RoomStatus;
use App\Models\BoardingHouse;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OwnerFinancialReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_financial_report_for_selected_month(): void
    {
        $owner = User::factory()->owner()->create();
        \App\Models\Subscription::factory()->for($owner)->create([
            'role' => \App\Enums\UserRole::Owner->value,
            'status' => \App\Enums\SubscriptionStatus::Active->value,
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
        ]);
        $otherOwner = User::factory()->owner()->create();

        $this->createPaidPayment($owner, 1250000, '2026-06-12 10:00:00', 'Kos Mawar', 'Siti Penyewa');
        $this->createPaidPayment($owner, 750000, '2026-06-20 11:00:00', 'Kos Melati', 'Ayu Penyewa');
        $this->createPaidPayment($owner, 500000, '2026-05-20 11:00:00', 'Kos Lama', 'Bulan Lalu');
        $this->createPaidPayment($otherOwner, 9000000, '2026-06-14 12:00:00', 'Kos Owner Lain', 'Penyewa Lain');

        $response = $this->actingAs($owner)->get(route('owner.reports.financial', [
            'month' => '2026-06',
        ]));

        $response->assertOk();
        $response->assertSee('Laporan Keuangan');
        $response->assertSee('Rp2.000.000');
        $response->assertSee('Kos Mawar');
        $response->assertSee('Kos Melati');
        $response->assertSee('Collection Rate');
        $response->assertSee('80.0%', false);
        $response->assertSee('MRR Aktif (Estimasi)');
        $response->assertSee('Proyeksi Bulan Depan');
        $response->assertSee('Analisis Piutang (Aging)');
        $response->assertDontSee('Kos Lama');
        $response->assertDontSee('Kos Owner Lain');
        $response->assertDontSee('Rp9.000.000');
    }

    public function test_non_owner_cannot_view_owner_financial_report(): void
    {
        $tenant = User::factory()->tenant()->create();

        $response = $this->actingAs($tenant)->get(route('owner.reports.financial'));

        $response->assertForbidden();
    }

    public function test_owner_dashboard_links_to_financial_report(): void
    {
        $owner = User::factory()->owner()->create();
        \App\Models\Subscription::factory()->for($owner)->create([
            'role' => \App\Enums\UserRole::Owner->value,
            'status' => \App\Enums\SubscriptionStatus::Active->value,
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
        ]);

        $response = $this->actingAs($owner)->get(route('owner.dashboard'));

        $response->assertOk();
        $response->assertSee('Laporan Keuangan');
        $response->assertSee(route('owner.reports.financial'), false);
    }

    public function test_owner_can_request_ai_financial_analysis_using_trial_credit(): void
    {
        config([
            'services.gemini.key' => 'test-gemini-key',
            'services.gemini.model' => 'gemini-2.5-flash',
        ]);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [[
                            'text' => json_encode([
                                'summary' => 'Arus kas stabil dan okupansi perlu dipertahankan.',
                                'risks' => ['Piutang perlu dipantau.'],
                                'recommendations' => ['Kirim pengingat sebelum jatuh tempo.'],
                                'forecast_note' => 'Proyeksi mengikuti MRR dan collection rate saat ini.',
                            ], JSON_THROW_ON_ERROR),
                        ]],
                    ],
                ]],
            ]),
        ]);

        $owner = User::factory()->owner()->create();
        \App\Models\Subscription::factory()->for($owner)->create([
            'role' => \App\Enums\UserRole::Owner->value,
            'status' => \App\Enums\SubscriptionStatus::Active->value,
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
        ]);

        $response = $this->actingAs($owner)->post(route('owner.reports.financial.ai'), [
            'month' => '2026-06',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure(['insights']);

        $this->assertSame(4, $owner->refresh()->ai_trial_credits_remaining);
        $this->assertDatabaseHas('ai_usages', [
            'user_id' => $owner->id,
            'feature' => AiFeature::FinancialAnalysis->value,
            'source' => 'trial',
        ]);
    }

    private function createPaidPayment(
        User $owner,
        int $amount,
        string $paidAt,
        string $boardingHouseName,
        string $tenantName
    ): Payment {
        $tenant = User::factory()->tenant()->create(['name' => $tenantName]);
        $boardingHouse = BoardingHouse::factory()->published()->for($owner, 'owner')->create([
            'name' => $boardingHouseName,
        ]);
        $room = Room::factory()->for($boardingHouse)->create([
            'price_monthly' => $amount,
            'status' => RoomStatus::Occupied,
        ]);
        $booking = Booking::factory()->for($boardingHouse)->for($room)->for($tenant, 'tenant')->create([
            'status' => BookingStatus::Accepted,
        ]);
        $lease = Lease::factory()->for($booking)->for($boardingHouse)->for($room)->for($tenant, 'tenant')->for($owner, 'owner')->create([
            'status' => LeaseStatus::Active,
        ]);
        $invoice = Invoice::factory()->for($lease)->paid()->create([
            'amount' => $amount,
            'status' => InvoiceStatus::Paid,
        ]);

        return Payment::factory()->for($invoice)->create([
            'amount' => $amount,
            'status' => PaymentStatus::Paid,
            'paid_at' => $paidAt,
        ]);
    }
}
