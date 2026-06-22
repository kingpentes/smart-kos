<?php

namespace Tests\Feature\Complaints;

use App\Enums\BookingStatus;
use App\Enums\ComplaintStatus;
use App\Enums\LeaseStatus;
use App\Models\BoardingHouse;
use App\Models\Booking;
use App\Models\Complaint;
use App\Models\Lease;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComplaintFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_with_active_lease_can_create_complaint(): void
    {
        [$tenant, $lease] = $this->createActiveLease();

        $response = $this->actingAs($tenant)->post(route('tenant.complaints.store'), [
            'lease_id' => $lease->id,
            'category' => 'fasilitas_rusak',
            'description' => 'Keran kamar mandi rusak dan perlu diperbaiki.',
        ]);

        $complaint = Complaint::query()->firstOrFail();

        $response->assertRedirect(route('tenant.complaints.show', $complaint));
        $this->assertDatabaseHas('complaints', [
            'lease_id' => $lease->id,
            'tenant_id' => $tenant->id,
            'owner_id' => $lease->owner_id,
            'category' => 'fasilitas_rusak',
            'status' => ComplaintStatus::Open->value,
        ]);
    }

    public function test_tenant_cannot_create_complaint_for_another_tenant_lease(): void
    {
        [, $lease] = $this->createActiveLease();
        $otherTenant = User::factory()->tenant()->create();

        $response = $this->actingAs($otherTenant)
            ->from(route('tenant.complaints.create'))
            ->post(route('tenant.complaints.store'), [
                'lease_id' => $lease->id,
                'category' => 'keamanan',
                'description' => 'Pintu pagar sering terbuka.',
            ]);

        $response->assertRedirect(route('tenant.complaints.create'));
        $response->assertSessionHasErrors('lease_id');
        $this->assertDatabaseCount('complaints', 0);
    }

    public function test_owner_can_reply_and_update_complaint_status(): void
    {
        [$tenant, $lease, $owner] = $this->createActiveLease();
        $complaint = Complaint::factory()->create([
            'lease_id' => $lease->id,
            'tenant_id' => $tenant->id,
            'owner_id' => $owner->id,
            'status' => ComplaintStatus::Open,
        ]);

        $response = $this->actingAs($owner)->post(route('owner.complaints.reply', $complaint), [
            'message' => 'Akan kami cek hari ini.',
            'status' => ComplaintStatus::InProgress->value,
        ]);

        $response->assertRedirect(route('owner.complaints.show', $complaint));
        $this->assertDatabaseHas('complaint_replies', [
            'complaint_id' => $complaint->id,
            'user_id' => $owner->id,
            'message' => 'Akan kami cek hari ini.',
        ]);
        $this->assertDatabaseHas('complaints', [
            'id' => $complaint->id,
            'status' => ComplaintStatus::InProgress->value,
        ]);
    }

    public function test_tenant_can_reply_to_own_complaint(): void
    {
        [$tenant, $lease, $owner] = $this->createActiveLease();
        $complaint = Complaint::factory()->create([
            'lease_id' => $lease->id,
            'tenant_id' => $tenant->id,
            'owner_id' => $owner->id,
        ]);

        $response = $this->actingAs($tenant)->post(route('tenant.complaints.reply', $complaint), [
            'message' => 'Terima kasih, saya tunggu kabarnya.',
        ]);

        $response->assertRedirect(route('tenant.complaints.show', $complaint));
        $this->assertDatabaseHas('complaint_replies', [
            'complaint_id' => $complaint->id,
            'user_id' => $tenant->id,
            'message' => 'Terima kasih, saya tunggu kabarnya.',
        ]);
    }

    public function test_owner_cannot_view_another_owner_complaint(): void
    {
        [$tenant, $lease, $owner] = $this->createActiveLease();
        $otherOwner = User::factory()->owner()->create();
        \App\Models\Subscription::factory()->for($otherOwner)->create([
            'role' => \App\Enums\UserRole::Owner->value,
            'status' => \App\Enums\SubscriptionStatus::Active->value,
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
        ]);
        $complaint = Complaint::factory()->create([
            'lease_id' => $lease->id,
            'tenant_id' => $tenant->id,
            'owner_id' => $owner->id,
        ]);

        $response = $this->actingAs($otherOwner)->get(route('owner.complaints.show', $complaint));

        $response->assertForbidden();
    }

    /**
     * @return array{0: User, 1: Lease, 2: User}
     */
    private function createActiveLease(): array
    {
        $tenant = User::factory()->tenant()->create();
        $owner = User::factory()->owner()->create();
        \App\Models\Subscription::factory()->for($owner)->create([
            'role' => \App\Enums\UserRole::Owner->value,
            'status' => \App\Enums\SubscriptionStatus::Active->value,
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
        ]);
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
