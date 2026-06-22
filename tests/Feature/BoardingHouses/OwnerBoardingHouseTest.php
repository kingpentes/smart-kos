<?php

namespace Tests\Feature\BoardingHouses;

use App\Enums\BoardingHouseStatus;
use App\Enums\BoardingHouseType;
use App\Models\BoardingHouse;
use App\Models\Facility;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerBoardingHouseTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_boarding_house_as_draft(): void
    {
        $owner = User::factory()->owner()->create();
        \App\Models\Subscription::factory()->for($owner)->create([
            'role' => \App\Enums\UserRole::Owner->value,
            'status' => \App\Enums\SubscriptionStatus::Active->value,
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
        ]);
        $facility = Facility::factory()->create(['name' => 'WiFi', 'slug' => 'wifi']);

        $response = $this->actingAs($owner)->post(route('owner.listings.store'), [
            'name' => 'Kos Mawar',
            'description' => 'Kos nyaman dekat kampus.',
            'address' => 'Jl. Telekomunikasi No. 1',
            'city' => 'Bandung',
            'district' => 'Sukapura',
            'type' => BoardingHouseType::Female->value,
            'price_monthly' => 900000,
            'deposit_amount' => 100000,
            'room_count' => 3,
            'facilities' => [$facility->id],
            'rules' => [
                ['key' => 'Jam malam', 'value' => 'Maksimal pukul 22.00.'],
            ],
        ]);

        $boardingHouse = BoardingHouse::query()->where('name', 'Kos Mawar')->firstOrFail();

        $response->assertRedirect(route('owner.listings.edit', $boardingHouse));
        $this->assertDatabaseHas('boarding_houses', [
            'owner_id' => $owner->id,
            'name' => 'Kos Mawar',
            'status' => BoardingHouseStatus::Draft->value,
        ]);
        $this->assertDatabaseCount('rooms', 3);
        $this->assertDatabaseHas('boarding_house_facility', [
            'boarding_house_id' => $boardingHouse->id,
            'facility_id' => $facility->id,
        ]);
    }

    public function test_owner_can_submit_own_listing_for_verification(): void
    {
        $owner = User::factory()->owner()->create();
        \App\Models\Subscription::factory()->for($owner)->create([
            'role' => \App\Enums\UserRole::Owner->value,
            'status' => \App\Enums\SubscriptionStatus::Active->value,
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
        ]);
        $boardingHouse = BoardingHouse::factory()->for($owner, 'owner')->create();

        $response = $this->actingAs($owner)->patch(route('owner.listings.submit', $boardingHouse));

        $response->assertRedirect(route('owner.listings.index'));
        $this->assertDatabaseHas('boarding_houses', [
            'id' => $boardingHouse->id,
            'status' => BoardingHouseStatus::Pending->value,
        ]);
    }

    public function test_owner_cannot_edit_listing_owned_by_another_owner(): void
    {
        $owner = User::factory()->owner()->create();
        \App\Models\Subscription::factory()->for($owner)->create([
            'role' => \App\Enums\UserRole::Owner->value,
            'status' => \App\Enums\SubscriptionStatus::Active->value,
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
        ]);
        $otherOwner = User::factory()->owner()->create();
        $boardingHouse = BoardingHouse::factory()->for($otherOwner, 'owner')->create();

        $response = $this->actingAs($owner)->get(route('owner.listings.edit', $boardingHouse));

        $response->assertForbidden();
    }

    public function test_tenant_cannot_access_owner_listing_page(): void
    {
        $tenant = User::factory()->tenant()->create();

        $response = $this->actingAs($tenant)->get(route('owner.listings.create'));

        $response->assertForbidden();
    }
}
