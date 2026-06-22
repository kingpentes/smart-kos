<?php

namespace App\Http\Controllers\Owner;

use App\Actions\BoardingHouses\StoreBoardingHouse;
use App\Actions\BoardingHouses\SubmitBoardingHouse;
use App\Actions\BoardingHouses\UpdateBoardingHouse;
use App\Http\Controllers\Controller;
use App\Http\Requests\BoardingHouses\StoreBoardingHouseRequest;
use App\Http\Requests\BoardingHouses\UpdateBoardingHouseRequest;
use App\Models\BoardingHouse;
use App\Models\Facility;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BoardingHouseController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', BoardingHouse::class);

        return view('owner.boarding-houses.index', [
            'boardingHouses' => $request->user()
                ->boardingHouses()
                ->withCount('rooms')
                ->latest()
                ->paginate(10),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', BoardingHouse::class);

        return view('owner.boarding-houses.create', [
            'facilities' => Facility::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreBoardingHouseRequest $request, StoreBoardingHouse $storeBoardingHouse): RedirectResponse
    {
        $this->authorize('create', BoardingHouse::class);

        $boardingHouse = $storeBoardingHouse->handle($request->user(), $request->validated());

        return redirect()
            ->route('owner.listings.edit', $boardingHouse)
            ->with('status', 'Listing kos tersimpan sebagai draft.');
    }

    public function show(BoardingHouse $boardingHouse): View
    {
        $this->authorize('view', $boardingHouse);

        return view('owner.boarding-houses.show', [
            'boardingHouse' => $boardingHouse->load([
                'rooms' => function ($query) {
                    $query->with(['activeLease.tenant', 'activeLease.invoices']);
                },
            ]),
        ]);
    }

    public function edit(BoardingHouse $boardingHouse): View
    {
        $this->authorize('update', $boardingHouse);

        return view('owner.boarding-houses.edit', [
            'boardingHouse' => $boardingHouse->load(['facilities', 'rules']),
            'facilities' => Facility::query()->orderBy('name')->get(),
        ]);
    }

    public function update(
        UpdateBoardingHouseRequest $request,
        BoardingHouse $boardingHouse,
        UpdateBoardingHouse $updateBoardingHouse
    ): RedirectResponse {
        $this->authorize('update', $boardingHouse);

        $updateBoardingHouse->handle($boardingHouse, $request->validated());

        return redirect()
            ->route('owner.listings.edit', $boardingHouse)
            ->with('status', 'Listing kos diperbarui.');
    }

    public function submit(BoardingHouse $boardingHouse, SubmitBoardingHouse $submitBoardingHouse): RedirectResponse
    {
        $this->authorize('submit', $boardingHouse);

        $submitBoardingHouse->handle($boardingHouse);

        return redirect()
            ->route('owner.listings.index')
            ->with('status', 'Listing dikirim untuk verifikasi admin.');
    }
}
