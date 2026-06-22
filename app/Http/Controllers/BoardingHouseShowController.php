<?php

namespace App\Http\Controllers;

use App\Enums\BoardingHouseStatus;
use App\Models\BoardingHouse;
use App\Services\Ai\AiAccessService;
use App\Services\Maps\OpenStreetMapUrlBuilder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BoardingHouseShowController extends Controller
{
    public function __invoke(
        Request $request,
        BoardingHouse $boardingHouse,
        OpenStreetMapUrlBuilder $mapUrlBuilder,
        AiAccessService $aiAccessService,
    ): View {
        abort_unless($boardingHouse->status === BoardingHouseStatus::Published, 404);

        return view('boarding-houses.show', [
            'boardingHouse' => $boardingHouse->load(['photos', 'facilities', 'rules', 'availableRooms', 'owner', 'reviews.tenant']),
            'mapData' => $boardingHouse->latitude && $boardingHouse->longitude
                ? $mapUrlBuilder->forCoordinates((float) $boardingHouse->latitude, (float) $boardingHouse->longitude)
                : null,
            'aiAccess' => $request->user() === null
                ? null
                : $aiAccessService->status($request->user()),
        ]);
    }
}
