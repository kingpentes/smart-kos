<?php

namespace App\Actions\Bookings;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Validation\ValidationException;

class RejectBooking
{
    /**
     * @throws ValidationException
     */
    public function handle(Booking $booking): Booking
    {
        if ($booking->status !== BookingStatus::Pending) {
            throw ValidationException::withMessages([
                'booking' => 'Booking ini sudah diproses.',
            ]);
        }

        $booking->update([
            'status' => BookingStatus::Rejected,
        ]);

        return $booking->refresh();
    }
}
