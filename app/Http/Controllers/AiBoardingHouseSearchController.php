<?php

namespace App\Http\Controllers;

use App\Actions\Ai\ConsumeAiUsage;
use App\Actions\BoardingHouses\ParseBoardingHouseSearchPrompt;
use App\Enums\AiFeature;
use App\Http\Requests\AiBoardingHouseSearchRequest;
use App\Models\Facility;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AiBoardingHouseSearchController extends Controller
{
    public function __invoke(
        AiBoardingHouseSearchRequest $request,
        ParseBoardingHouseSearchPrompt $parseSearchPrompt,
        ConsumeAiUsage $consumeAiUsage,
    ): RedirectResponse {
        $prompt = $request->validated('prompt');
        $usage = $consumeAiUsage->handle($request->user(), AiFeature::BoardingHouseSearch, [
            'prompt' => $prompt,
        ]);
        $criteria = $parseSearchPrompt->handle(
            $prompt,
            Facility::query()->orderBy('name')->get(),
        );

        if ($criteria['source'] === 'failed') {
            $consumeAiUsage->refund($usage);

            throw ValidationException::withMessages([
                'prompt' => 'AI Finder sedang tidak tersedia. Silakan coba lagi beberapa saat.',
            ]);
        }

        $resultToken = (string) Str::uuid();
        $request->session()->put("ai_search_results.{$resultToken}", $criteria);

        return redirect()->route('boarding-houses.search', [
            'ai_result' => $resultToken,
            'prompt' => $prompt,
        ]);
    }
}
