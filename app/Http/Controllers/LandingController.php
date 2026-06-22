<?php

namespace App\Http\Controllers;

use App\Models\BoardingHouse;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function __invoke(): View
    {
        return view('landing', [
            'boardingHouses' => BoardingHouse::query()
                ->published()
                ->with(['primaryPhoto', 'facilities'])
                ->latest()
                ->limit(6)
                ->get(),
        ]);
    }
}
