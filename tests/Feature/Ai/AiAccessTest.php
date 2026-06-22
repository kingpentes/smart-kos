<?php

namespace Tests\Feature\Ai;

use App\Enums\AiFeature;
use App\Models\BoardingHouse;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.gemini.key' => 'test-gemini-key',
            'services.gemini.model' => 'gemini-2.5-flash',
        ]);
    }

    public function test_user_gets_two_free_area_analysis_requests_then_must_subscribe(): void
    {
        $user = User::factory()->tenant()->create(['ai_trial_credits_remaining' => 2]);
        $boardingHouse = BoardingHouse::factory()->published()->create([
            'latitude' => -1.265386,
            'longitude' => 116.831200,
        ]);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response($this->areaReviewResponse()),
        ]);

        $this->actingAs($user)
            ->postJson(route('api.ai-review', $boardingHouse))
            ->assertOk()
            ->assertJsonPath('score', 85);

        $this->actingAs($user)
            ->postJson(route('api.ai-review', $boardingHouse))
            ->assertOk();

        $this->actingAs($user)
            ->postJson(route('api.ai-review', $boardingHouse))
            ->assertStatus(402)
            ->assertJsonPath('subscribe_url', route('subscriptions.index'));

        $this->assertSame(0, $user->refresh()->ai_trial_credits_remaining);
        $this->assertDatabaseCount('ai_usages', 2);
    }

    public function test_active_subscription_is_used_after_trial_credits_are_spent(): void
    {
        $user = User::factory()->tenant()->create([
            'ai_trial_credits_remaining' => 0,
        ]);
        $subscription = Subscription::factory()->for($user)->create([
            'ai_request_limit' => 50,
            'ai_requests_used' => 49,
        ]);
        $boardingHouse = BoardingHouse::factory()->published()->create();

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response($this->areaReviewResponse()),
        ]);

        $this->actingAs($user)
            ->postJson(route('api.ai-review', $boardingHouse))
            ->assertOk();

        $this->assertSame(50, $subscription->refresh()->ai_requests_used);
        $this->assertDatabaseHas('ai_usages', [
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'feature' => AiFeature::AreaReview->value,
            'source' => 'subscription',
        ]);

        $this->actingAs($user)
            ->postJson(route('api.ai-review', $boardingHouse))
            ->assertStatus(402);
    }

    public function test_ai_finder_consumes_one_trial_credit_and_redirects_to_saved_result(): void
    {
        $user = User::factory()->tenant()->create(['ai_trial_credits_remaining' => 2]);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [[
                            'text' => json_encode([
                                'location' => 'Balikpapan',
                                'price_max' => 1000000,
                                'type' => 'mixed',
                                'facilities' => [],
                                'target_latitude' => -1.265386,
                                'target_longitude' => 116.831200,
                            ], JSON_THROW_ON_ERROR),
                        ]],
                    ],
                ]],
            ]),
        ]);

        $response = $this->actingAs($user)->post(route('ai.boarding-houses.search'), [
            'prompt' => 'Kos campur di Balikpapan maksimal satu juta',
        ]);

        $response->assertRedirect();
        $this->assertStringContainsString('ai_result=', (string) $response->headers->get('Location'));
        $this->assertSame(1, $user->refresh()->ai_trial_credits_remaining);
        $this->assertDatabaseHas('ai_usages', [
            'user_id' => $user->id,
            'feature' => AiFeature::BoardingHouseSearch->value,
            'source' => 'trial',
        ]);
    }

    public function test_guest_cannot_call_ai_endpoints(): void
    {
        $boardingHouse = BoardingHouse::factory()->published()->create();

        $this->post(route('api.ai-review', $boardingHouse))->assertRedirect(route('login'));
        $this->post(route('ai.boarding-houses.search'), ['prompt' => 'Kos murah'])->assertRedirect(route('login'));
    }

    /**
     * @return array<string, mixed>
     */
    private function areaReviewResponse(): array
    {
        return [
            'candidates' => [[
                'content' => [
                    'parts' => [[
                        'text' => json_encode([
                            'review' => 'Area strategis dengan fasilitas yang cukup lengkap.',
                            'score' => 85,
                            'amenities' => [
                                'Warung Makan' => 12,
                                'Minimarket' => 4,
                                'Transportasi Umum' => 5,
                                'Fasilitas Kesehatan' => 2,
                            ],
                        ], JSON_THROW_ON_ERROR),
                    ]],
                ],
            ]],
        ];
    }
}
