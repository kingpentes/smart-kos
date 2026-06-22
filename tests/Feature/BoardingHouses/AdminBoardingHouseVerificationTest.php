<?php

namespace Tests\Feature\BoardingHouses;

use App\Enums\BoardingHouseStatus;
use App\Models\BoardingHouse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBoardingHouseVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_publish_pending_listing(): void
    {
        $admin = User::factory()->admin()->create();
        $boardingHouse = BoardingHouse::factory()->pending()->create();

        $response = $this->actingAs($admin)->patch(route('admin.listings.verify', $boardingHouse));

        $response->assertRedirect(route('admin.listings.index'));
        $this->assertDatabaseHas('boarding_houses', [
            'id' => $boardingHouse->id,
            'status' => BoardingHouseStatus::Published->value,
            'verified_by' => $admin->id,
        ]);
        $this->assertNotNull($boardingHouse->refresh()->verified_at);
    }

    public function test_admin_can_reject_pending_listing(): void
    {
        $admin = User::factory()->admin()->create();
        $boardingHouse = BoardingHouse::factory()->pending()->create();

        $response = $this->actingAs($admin)->patch(route('admin.listings.reject', $boardingHouse));

        $response->assertRedirect(route('admin.listings.index'));
        $this->assertDatabaseHas('boarding_houses', [
            'id' => $boardingHouse->id,
            'status' => BoardingHouseStatus::Rejected->value,
            'verified_by' => $admin->id,
        ]);
    }

    public function test_owner_cannot_open_admin_listing_verification(): void
    {
        $owner = User::factory()->owner()->create();

        $response = $this->actingAs($owner)->get(route('admin.listings.index'));

        $response->assertForbidden();
    }
}
