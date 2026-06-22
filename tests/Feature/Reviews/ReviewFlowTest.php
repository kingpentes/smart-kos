<?php

namespace Tests\Feature\Reviews;

use App\Enums\BookingStatus;
use App\Enums\LeaseStatus;
use App\Models\BoardingHouse;
use App\Models\Booking;
use App\Models\Lease;
use App\Models\Review;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_with_active_lease_can_create_review(): void
    {
        [$tenant, $lease] = $this->createActiveLease();

        $response = $this->actingAs($tenant)->post(route('tenant.reviews.store'), [
            'lease_id' => $lease->id,
            'cleanliness_rating' => 5,
            'security_rating' => 4,
            'photo_match_rating' => 3,
            'comment' => 'Foto cukup sesuai dan lingkungan aman.',
        ]);

        $response->assertRedirect(route('boarding-houses.show', $lease->boardingHouse));
        $this->assertDatabaseHas('reviews', [
            'lease_id' => $lease->id,
            'boarding_house_id' => $lease->boarding_house_id,
            'tenant_id' => $tenant->id,
            'cleanliness_rating' => 5,
            'security_rating' => 4,
            'photo_match_rating' => 3,
        ]);
    }

    public function test_tenant_cannot_create_review_for_another_tenant_lease(): void
    {
        [, $lease] = $this->createActiveLease();
        $otherTenant = User::factory()->tenant()->create();

        $response = $this->actingAs($otherTenant)
            ->from(route('tenant.reviews.create'))
            ->post(route('tenant.reviews.store'), [
                'lease_id' => $lease->id,
                'cleanliness_rating' => 5,
                'security_rating' => 5,
                'photo_match_rating' => 5,
            ]);

        $response->assertRedirect(route('tenant.reviews.create'));
        $response->assertSessionHasErrors('lease_id');
        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_tenant_can_only_review_a_lease_once(): void
    {
        [$tenant, $lease] = $this->createActiveLease();
        Review::factory()->create([
            'lease_id' => $lease->id,
            'boarding_house_id' => $lease->boarding_house_id,
            'tenant_id' => $tenant->id,
        ]);

        $response = $this->actingAs($tenant)
            ->from(route('tenant.reviews.create'))
            ->post(route('tenant.reviews.store'), [
                'lease_id' => $lease->id,
                'cleanliness_rating' => 4,
                'security_rating' => 4,
                'photo_match_rating' => 4,
            ]);

        $response->assertRedirect(route('tenant.reviews.create'));
        $response->assertSessionHasErrors('lease_id');
        $this->assertDatabaseCount('reviews', 1);
    }

    public function test_boarding_house_detail_shows_trust_score_after_review_created(): void
    {
        [$tenant, $lease] = $this->createActiveLease();

        $this->actingAs($tenant)->post(route('tenant.reviews.store'), [
            'lease_id' => $lease->id,
            'cleanliness_rating' => 5,
            'security_rating' => 4,
            'photo_match_rating' => 3,
            'comment' => 'Sesuai dengan foto listing.',
        ]);

        $response = $this->get(route('boarding-houses.show', $lease->boardingHouse));

        $response->assertOk();
        $response->assertSee('Trust Score');
        $response->assertSee('4.0');
        $response->assertSee('Sesuai dengan foto listing.');
    }

    /**
     * @return array{0: User, 1: Lease, 2: User}
     */
    private function createActiveLease(): array
    {
        $tenant = User::factory()->tenant()->create();
        $owner = User::factory()->owner()->create();
        $boardingHouse = BoardingHouse::factory()->published()->for($owner, 'owner')->create();
        $room = Room::factory()->for($boardingHouse)->create();
        $booking = Booking::factory()->for($boardingHouse)->for($room)->for($tenant, 'tenant')->create([
            'status' => BookingStatus::Accepted,
        ]);
        $lease = Lease::factory()->for($booking)->for($boardingHouse)->for($room)->for($tenant, 'tenant')->for($owner, 'owner')->create([
            'status' => LeaseStatus::Active,
        ]);

        return [$tenant, $lease, $owner];
    }
}
