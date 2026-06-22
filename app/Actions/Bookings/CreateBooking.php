<?php

namespace App\Actions\Bookings;

use App\Enums\BoardingHouseStatus;
use App\Enums\BookingStatus;
use App\Enums\RoomStatus;
use App\Models\BoardingHouse;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateBooking
{
    /**
     * @param  array{room_id: int, start_date: string, duration_months: int, notes?: string|null}  $data
     *
     * @throws ValidationException
     */
    public function handle(User $tenant, BoardingHouse $boardingHouse, array $data): Booking
    {
        if ($boardingHouse->status !== BoardingHouseStatus::Published) {
            throw ValidationException::withMessages([
                'boarding_house' => 'Kos ini belum tersedia untuk booking.',
            ]);
        }

        return DB::transaction(function () use ($tenant, $boardingHouse, $data): Booking {
            $room = $boardingHouse->rooms()
                ->whereKey($data['room_id'])
                ->lockForUpdate()
                ->first();

            if (! $room || $room->status !== RoomStatus::Available) {
                throw ValidationException::withMessages([
                    'room_id' => 'Kamar tidak tersedia.',
                ]);
            }

            $hasPendingBooking = Booking::query()
                ->where('room_id', $room->id)
                ->where('status', BookingStatus::Pending->value)
                ->exists();

            if ($hasPendingBooking) {
                throw ValidationException::withMessages([
                    'room_id' => 'Kamar ini sedang menunggu konfirmasi booking lain.',
                ]);
            }

            return Booking::query()->create([
                'boarding_house_id' => $boardingHouse->id,
                'room_id' => $room->id,
                'tenant_id' => $tenant->id,
                'start_date' => $data['start_date'],
                'duration_months' => $data['duration_months'],
                'status' => BookingStatus::Pending,
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }
}
