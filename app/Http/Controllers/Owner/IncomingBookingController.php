<?php

namespace App\Http\Controllers\Owner;

use App\Actions\Bookings\AcceptBooking;
use App\Actions\Bookings\RejectBooking;
use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Notifications\BookingAcceptedNotification;
use App\Notifications\BookingRejectedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncomingBookingController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Booking::class);

        return view('owner.bookings.index', [
            'bookings' => Booking::query()
                ->whereHas('boardingHouse', fn ($query) => $query->where('owner_id', $request->user()->id))
                ->with(['boardingHouse', 'room', 'tenant'])
                ->where('status', BookingStatus::Pending->value)
                ->latest()
                ->paginate(10),
        ]);
    }

    public function accept(Booking $booking, AcceptBooking $acceptBooking): RedirectResponse
    {
        $this->authorize('accept', $booking);

        $acceptBooking->handle($booking);

        $booking->tenant->notify(new BookingAcceptedNotification($booking));

        return redirect()
            ->route('owner.bookings.index')
            ->with('status', 'Booking diterima dan sewa aktif dibuat.');
    }

    public function reject(Booking $booking, RejectBooking $rejectBooking): RedirectResponse
    {
        $this->authorize('reject', $booking);

        $rejectBooking->handle($booking);

        $booking->tenant->notify(new BookingRejectedNotification($booking));

        return redirect()
            ->route('owner.bookings.index')
            ->with('status', 'Booking ditolak.');
    }
}
