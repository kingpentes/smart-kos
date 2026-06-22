<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\Bookings\CreateBooking;
use App\Http\Controllers\Controller;
use App\Http\Requests\Bookings\StoreBookingRequest;
use App\Models\BoardingHouse;
use App\Models\Booking;
use App\Notifications\NewBookingNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $bookings = Booking::with(['boardingHouse', 'room', 'invoice'])
            ->where('tenant_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return view('tenant.bookings.index', compact('bookings'));
    }

    public function create(BoardingHouse $boardingHouse): View
    {
        $this->authorize('createFor', [Booking::class, $boardingHouse]);

        return view('tenant.bookings.create', [
            'boardingHouse' => $boardingHouse->load(['availableRooms', 'facilities']),
        ]);
    }

    public function store(
        StoreBookingRequest $request,
        BoardingHouse $boardingHouse,
        CreateBooking $createBooking
    ): RedirectResponse {
        $this->authorize('createFor', [Booking::class, $boardingHouse]);

        $booking = $createBooking->handle($request->user(), $boardingHouse, $request->validated());

        $boardingHouse->owner->notify(new NewBookingNotification($booking));

        return redirect()
            ->route('tenant.dashboard')
            ->with('status', "Booking {$booking->boardingHouse->name} berhasil dikirim. Tagihan akan muncul setelah pemilik menerima booking.");
    }
}
