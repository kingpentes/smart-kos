<?php

namespace App\Http\Controllers;

use App\Actions\Ai\ConsumeAiUsage;
use App\Enums\AiFeature;
use App\Models\BoardingHouse;
use App\Services\AiReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AiReviewController extends Controller
{
    public function show(
        Request $request,
        BoardingHouse $boardingHouse,
        AiReviewService $aiReviewService,
        ConsumeAiUsage $consumeAiUsage,
    ): JsonResponse {
        try {
            $usage = $consumeAiUsage->handle($request->user(), AiFeature::AreaReview, [
                'boarding_house_id' => $boardingHouse->id,
            ]);
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => $exception->errors()['ai'][0],
                'subscribe_url' => route('subscriptions.index'),
            ], 402);
        }

        $review = $aiReviewService->generateAreaReview($boardingHouse);

        if ($review['source'] === 'fallback') {
            $consumeAiUsage->refund($usage);
        }

        return response()->json($review);
    }
}
