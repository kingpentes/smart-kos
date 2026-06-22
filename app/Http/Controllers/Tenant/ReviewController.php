<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\Reviews\CreateReview;
use App\Enums\LeaseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reviews\StoreReviewRequest;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function create(Request $request): View
    {
        $this->authorize('create', Review::class);

        return view('tenant.reviews.create', [
            'leases' => $request->user()
                ->leases()
                ->whereIn('status', [LeaseStatus::Active->value, LeaseStatus::Ended->value])
                ->whereDoesntHave('review')
                ->with(['boardingHouse', 'room'])
                ->latest()
                ->get(),
            'selectedLeaseId' => $request->integer('lease_id') ?: null,
        ]);
    }

    public function store(StoreReviewRequest $request, CreateReview $createReview): RedirectResponse
    {
        $this->authorize('create', Review::class);

        $review = $createReview->handle($request->user(), $request->validated());

        return redirect()
            ->route('boarding-houses.show', $review->boardingHouse)
            ->with('status', 'Ulasan berhasil dikirim.');
    }
}
