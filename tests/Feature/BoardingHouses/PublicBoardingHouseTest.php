<?php

namespace Tests\Feature\BoardingHouses;

use App\Enums\BoardingHouseType;
use App\Models\BoardingHouse;
use App\Models\BoardingHousePhoto;
use App\Models\Facility;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PublicBoardingHouseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.gemini.key' => null]);
    }

    public function test_search_only_shows_published_listings(): void
    {
        BoardingHouse::factory()->published()->create(['name' => 'Kos Published']);
        BoardingHouse::factory()->pending()->create(['name' => 'Kos Pending']);

        $response = $this->get(route('boarding-houses.search'));

        $response->assertOk();
        $response->assertSee('Kos Published');
        $response->assertDontSee('Kos Pending');
    }

    public function test_public_detail_returns_not_found_for_unpublished_listing(): void
    {
        $boardingHouse = BoardingHouse::factory()->pending()->create();

        $response = $this->get(route('boarding-houses.show', $boardingHouse));

        $response->assertNotFound();
    }

    public function test_public_detail_shows_in_app_map_and_nearby_poi_links(): void
    {
        $boardingHouse = BoardingHouse::factory()->published()->create([
            'name' => 'Kos Peta',
            'latitude' => -6.9731234,
            'longitude' => 107.6305678,
        ]);

        $response = $this->get(route('boarding-houses.show', $boardingHouse));

        $response->assertOk();
        $response->assertSee('Lokasi & Sekitar', false);
        $response->assertSee('Peta lokasi Kos Peta');
        $response->assertSee('https://www.openstreetmap.org/export/embed.html', false);
        $response->assertSee('Minimarket');
        $response->assertSee('Kampus');
        $response->assertSee('Klinik');
        $response->assertSee('ATM');
        $response->assertSee('Tempat makan');
    }

    public function test_public_pages_render_boarding_house_photos(): void
    {
        $boardingHouse = BoardingHouse::factory()->published()->create([
            'name' => 'Kos Foto',
        ]);
        BoardingHousePhoto::factory()->primary()->for($boardingHouse)->create([
            'path' => 'boarding-houses/kos-foto-depan.jpg',
        ]);
        BoardingHousePhoto::factory()->for($boardingHouse)->create([
            'path' => 'boarding-houses/kos-foto-kamar.jpg',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('/storage/boarding-houses/kos-foto-depan.jpg', false)
            ->assertSee('Foto Kos Foto');

        $this->get(route('boarding-houses.search'))
            ->assertOk()
            ->assertSee('/storage/boarding-houses/kos-foto-depan.jpg', false)
            ->assertSee('Foto Kos Foto');

        $this->get(route('boarding-houses.show', $boardingHouse))
            ->assertOk()
            ->assertSee('/storage/boarding-houses/kos-foto-depan.jpg', false)
            ->assertSee('/storage/boarding-houses/kos-foto-kamar.jpg', false)
            ->assertSee('Foto utama Kos Foto');
    }

    public function test_search_can_filter_by_location_price_type_and_facility(): void
    {
        $wifi = Facility::factory()->create(['name' => 'WiFi', 'slug' => 'wifi']);
        $ac = Facility::factory()->create(['name' => 'AC', 'slug' => 'ac']);

        $matching = BoardingHouse::factory()->published()->create([
            'name' => 'Kos Cocok',
            'city' => 'Bandung',
            'district' => 'Sukapura',
            'type' => BoardingHouseType::Female,
            'price_monthly' => 850000,
        ]);
        $matching->facilities()->sync([$wifi->id]);

        $other = BoardingHouse::factory()->published()->create([
            'name' => 'Kos Tidak Cocok',
            'city' => 'Jakarta',
            'type' => BoardingHouseType::Male,
            'price_monthly' => 2000000,
        ]);
        $other->facilities()->sync([$ac->id]);

        $response = $this->get(route('boarding-houses.search', [
            'location' => 'Bandung',
            'type' => BoardingHouseType::Female->value,
            'price_max' => 1000000,
            'facilities' => [$wifi->id],
        ]));

        $response->assertOk();
        $response->assertSee('Kos Cocok');
        $response->assertDontSee('Kos Tidak Cocok');
    }

    public function test_ai_search_failure_does_not_consume_trial_credit(): void
    {
        config([
            'services.gemini.key' => 'testing-gemini-key',
            'services.gemini.model' => 'gemini-2.5-flash',
        ]);

        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent' => Http::response(null, 500),
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent' => Http::response(null, 500),
            '*' => Http::response(null, 500),
        ]);

        Facility::factory()->create(['name' => 'WiFi', 'slug' => 'wifi']);
        $tenant = User::factory()->tenant()->create(['ai_trial_credits_remaining' => 2]);

        $boardingHouse1 = BoardingHouse::factory()->published()->create([
            'name' => 'Kos A',
        ]);

        $boardingHouse2 = BoardingHouse::factory()->published()->create([
            'name' => 'Kos B',
        ]);

        $response = $this->actingAs($tenant)->post(route('ai.boarding-houses.search'), [
            'prompt' => 'kos dekat Telkom University, budget 800rb, ada wifi putri',
        ]);

        $response->assertSessionHasErrors('prompt');
        $this->assertSame(2, $tenant->refresh()->ai_trial_credits_remaining);
        $this->assertDatabaseCount('ai_usages', 0);
    }

    public function test_search_prompt_can_use_gemini_response_for_recommendation_criteria(): void
    {
        config([
            'services.gemini.key' => 'testing-gemini-key',
            'services.gemini.model' => 'gemini-2.5-flash',
        ]);

        Http::fake([
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'location' => 'Telkom University',
                                        'price_max' => 800000,
                                        'type' => 'female',
                                        'facilities' => ['WiFi'],
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $wifi = Facility::factory()->create(['name' => 'WiFi', 'slug' => 'wifi']);
        $ac = Facility::factory()->create(['name' => 'AC', 'slug' => 'ac']);

        $matching = BoardingHouse::factory()->published()->create([
            'name' => 'Kos Gemini Cocok',
            'address' => 'Jalan Telekomunikasi dekat Telkom University',
            'city' => 'Bandung',
            'district' => 'Sukapura',
            'type' => BoardingHouseType::Female,
            'price_monthly' => 750000,
        ]);
        $matching->facilities()->sync([$wifi->id]);

        $lowerRanked = BoardingHouse::factory()->published()->create([
            'name' => 'Kos Gemini Rendah',
            'address' => 'Jalan Telekomunikasi dekat Telkom University',
            'city' => 'Bandung',
            'district' => 'Sukapura',
            'type' => BoardingHouseType::Male,
            'price_monthly' => 2000000,
        ]);
        $lowerRanked->facilities()->sync([$ac->id]);

        $tenant = User::factory()->tenant()->create(['ai_trial_credits_remaining' => 2]);

        $response = $this->actingAs($tenant)->followingRedirects()->post(route('ai.boarding-houses.search'), [
            'prompt' => 'saya cari kos nyaman dekat kampus dengan budget minim',
        ]);

        $response->assertOk();
        $response->assertSeeInOrder(['Kos Gemini Cocok', 'Kos Gemini Rendah']);
        $response->assertSee('Hasil rekomendasi cerdas AI');
        $response->assertSee('Skor AI: 100');
        $this->assertSame(1, $tenant->refresh()->ai_trial_credits_remaining);

        Http::assertSent(fn ($request): bool => $request->hasHeader('x-goog-api-key', 'testing-gemini-key')
            && $request->url() === 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent');
    }
}
