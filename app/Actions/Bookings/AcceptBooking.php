<?php

namespace App\Actions\Bookings;

use App\Actions\Billing\CreateInitialInvoice;
use App\Enums\BookingStatus;
use App\Enums\LeaseStatus;
use App\Enums\RoomStatus;
use App\Models\Booking;
use App\Models\Lease;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AcceptBooking
{
    public function __construct(private CreateInitialInvoice $createInitialInvoice) {}

    /**
     * @throws ValidationException
     */
    public function handle(Booking $booking): Lease
    {
        return DB::transaction(function () use ($booking): Lease {
            $booking = Booking::query()
                ->with(['boardingHouse', 'room'])
                ->lockForUpdate()
                ->findOrFail($booking->id);

            if ($booking->status !== BookingStatus::Pending) {
                throw ValidationException::withMessages([
                    'booking' => 'Booking ini sudah diproses.',
                ]);
            }

            $room = $booking->room()->lockForUpdate()->firstOrFail();

            if ($room->status !== RoomStatus::Available) {
                throw ValidationException::withMessages([
                    'booking' => 'Kamar sudah tidak tersedia.',
                ]);
            }

            $startDate = $booking->start_date->copy();
            $endDate = $startDate->copy()->addMonthsNoOverflow($booking->duration_months)->subDay();
            $nextDueDate = $startDate->copy()->addMonthNoOverflow();

            $lease = Lease::query()->create([
                'booking_id' => $booking->id,
                'boarding_house_id' => $booking->boarding_house_id,
                'room_id' => $booking->room_id,
                'tenant_id' => $booking->tenant_id,
                'owner_id' => $booking->boardingHouse->owner_id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'next_due_date' => $nextDueDate,
                'status' => LeaseStatus::Active,
            ]);

            $booking->update([
                'status' => BookingStatus::Accepted,
            ]);

            $room->update([
                'status' => RoomStatus::Occupied,
            ]);

            Booking::query()
                ->where('room_id', $room->id)
                ->where('id', '!=', $booking->id)
                ->where('status', BookingStatus::Pending->value)
                ->update(['status' => BookingStatus::Rejected]);

            $this->createInitialInvoice->handle($lease);

            return $lease;
        });
    }
}
