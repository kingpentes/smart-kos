<?php

namespace Tests\Feature\Bookings;

use App\Enums\BookingStatus;
use App\Enums\RoomStatus;
use App\Models\BoardingHouse;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_can_create_booking_for_available_room(): void
    {
        $tenant = User::factory()->tenant()->create();
        $boardingHouse = BoardingHouse::factory()->published()->create();
        $room = Room::factory()->for($boardingHouse)->create([
            'room_number' => 'A1',
            'status' => RoomStatus::Available,
        ]);

        $response = $this->actingAs($tenant)->post(route('tenant.bookings.store', $boardingHouse), [
            'room_id' => $room->id,
            'start_date' => now()->addDays(7)->toDateString(),
            'duration_months' => 3,
            'notes' => 'Saya ingin masuk awal bulan.',
        ]);

        $response->assertRedirect(route('tenant.dashboard'));
        $response->assertSessionHas('status', "Booking {$boardingHouse->name} berhasil dikirim. Tagihan akan muncul setelah pemilik menerima booking.");
        $this->assertDatabaseHas('bookings', [
            'boarding_house_id' => $boardingHouse->id,
            'room_id' => $room->id,
            'tenant_id' => $tenant->id,
            'duration_months' => 3,
            'status' => BookingStatus::Pending->value,
        ]);

        $this->actingAs($tenant)
            ->get(route('tenant.dashboard'))
            ->assertOk()
            ->assertSee('Menunggu Konfirmasi Pemilik')
            ->assertSee('Booking masuk. Anda dapat membayar setelah pemilik menerima booking.');
    }

    public function test_tenant_cannot_book_occupied_room(): void
    {
        $tenant = User::factory()->tenant()->create();
        $boardingHouse = BoardingHouse::factory()->published()->create();
        $room = Room::factory()->for($boardingHouse)->occupied()->create();

        $response = $this->actingAs($tenant)
            ->from(route('tenant.bookings.create', $boardingHouse))
            ->post(route('tenant.bookings.store', $boardingHouse), [
                'room_id' => $room->id,
                'start_date' => now()->addDays(7)->toDateString(),
                'duration_months' => 1,
            ]);

        $response->assertRedirect(route('tenant.bookings.create', $boardingHouse));
        $response->assertSessionHasErrors('room_id');
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_owner_accepting_booking_creates_active_lease_and_occupies_room(): void
    {
        $owner = User::factory()->owner()->create();
        $tenant = User::factory()->tenant()->create();
        $boardingHouse = BoardingHouse::factory()->published()->for($owner, 'owner')->create();
        $room = Room::factory()->for($boardingHouse)->create(['status' => RoomStatus::Available]);
        $booking = Booking::factory()->for($boardingHouse)->for($room)->for($tenant, 'tenant')->create([
            'duration_months' => 6,
            'status' => BookingStatus::Pending,
        ]);

        $response = $this->actingAs($owner)->patch(route('owner.bookings.accept', $booking));

        $response->assertRedirect(route('owner.bookings.index'));
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => BookingStatus::Accepted->value,
        ]);
        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'status' => RoomStatus::Occupied->value,
        ]);
        $this->assertDatabaseHas('leases', [
            'booking_id' => $booking->id,
            'boarding_house_id' => $boardingHouse->id,
            'room_id' => $room->id,
            'tenant_id' => $tenant->id,
            'owner_id' => $owner->id,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('invoices', [
            'status' => 'unpaid',
            'amount' => $room->price_monthly,
        ]);
        $response = $this->actingAs($tenant)
            ->get(route('tenant.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Tagihan Belum Dibayar');
        $response->assertSee($booking->boardingHouse->name);
    }

    public function test_owner_rejecting_booking_does_not_create_lease(): void
    {
        $owner = User::factory()->owner()->create();
        $tenant = User::factory()->tenant()->create();
        $boardingHouse = BoardingHouse::factory()->published()->for($owner, 'owner')->create();
        $room = Room::factory()->for($boardingHouse)->create();
        $booking = Booking::factory()->for($boardingHouse)->for($room)->for($tenant, 'tenant')->create([
            'status' => BookingStatus::Pending,
        ]);

        $response = $this->actingAs($owner)->patch(route('owner.bookings.reject', $booking));

        $response->assertRedirect(route('owner.bookings.index'));
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => BookingStatus::Rejected->value,
        ]);
        $this->assertDatabaseCount('leases', 0);
    }

    public function test_owner_cannot_accept_booking_for_another_owner_listing(): void
    {
        $owner = User::factory()->owner()->create();
        $otherOwner = User::factory()->owner()->create();
        $boardingHouse = BoardingHouse::factory()->published()->for($otherOwner, 'owner')->create();
        $room = Room::factory()->for($boardingHouse)->create();
        $booking = Booking::factory()->for($boardingHouse)->for($room)->create([
            'status' => BookingStatus::Pending,
        ]);

        $response = $this->actingAs($owner)->patch(route('owner.bookings.accept', $booking));

        $response->assertForbidden();
        $this->assertDatabaseCount('leases', 0);
    }
}
