<?php

namespace App\Actions\BoardingHouses;

use App\Models\Facility;
use App\Services\Ai\GeminiClient;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ParseBoardingHouseSearchPrompt
{
    public function __construct(private GeminiClient $geminiClient) {}

    /**
     * @param  Collection<int, Facility>  $facilities
     * @return array{
     *     prompt: string,
     *     normalized_prompt: string,
     *     location: string|null,
     *     price_max: int|null,
     *     type: string|null,
     *     facility_ids: array<int, int>,
     *     facility_names: array<int, string>,
     *     target_latitude: float|null,
     *     target_longitude: float|null,
     *     source: string
     * }
     */
    public function handle(string $prompt, Collection $facilities): array
    {
        $geminiCriteria = $this->geminiClient->parseBoardingHouseSearchPrompt($prompt, $facilities);

        if ($geminiCriteria !== null) {
            return $geminiCriteria;
        }

        Log::error('Gemini AI Finder failed to load or parse prompt.', ['prompt' => $prompt]);

        return [
            'prompt' => trim($prompt),
            'normalized_prompt' => Str::squish(Str::lower(Str::ascii($prompt))),
            'location' => null,
            'price_max' => null,
            'type' => null,
            'facility_ids' => [],
            'facility_names' => [],
            'target_latitude' => null,
            'target_longitude' => null,
            'source' => 'failed',
        ];
    }
}
