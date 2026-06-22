<?php

namespace App\Http\Controllers\Admin;

use App\Actions\BoardingHouses\RejectBoardingHouse;
use App\Actions\BoardingHouses\VerifyBoardingHouse;
use App\Enums\BoardingHouseStatus;
use App\Http\Controllers\Controller;
use App\Models\BoardingHouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BoardingHouseVerificationController extends Controller
{
    public function index(): View
    {
        $this->authorize('verify', BoardingHouse::class);

        return view('admin.boarding-houses.index', [
            'boardingHouses' => BoardingHouse::query()
                ->where('status', BoardingHouseStatus::Pending->value)
                ->with('owner')
                ->latest()
                ->paginate(10),
        ]);
    }

    public function verify(
        Request $request,
        BoardingHouse $boardingHouse,
        VerifyBoardingHouse $verifyBoardingHouse
    ): RedirectResponse {
        $this->authorize('verify', BoardingHouse::class);

        $verifyBoardingHouse->handle($boardingHouse, $request->user());

        return redirect()
            ->route('admin.listings.index')
            ->with('status', 'Listing berhasil dipublikasikan.');
    }

    public function reject(
        Request $request,
        BoardingHouse $boardingHouse,
        RejectBoardingHouse $rejectBoardingHouse
    ): RedirectResponse {
        $this->authorize('verify', BoardingHouse::class);

        $rejectBoardingHouse->handle($boardingHouse, $request->user());

        return redirect()
            ->route('admin.listings.index')
            ->with('status', 'Listing berhasil ditolak.');
    }
}
