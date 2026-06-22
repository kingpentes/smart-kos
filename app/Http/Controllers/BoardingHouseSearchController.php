<?php

namespace App\Http\Controllers;

use App\Models\BoardingHouse;
use App\Models\Facility;
use App\Services\Ai\AiAccessService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BoardingHouseSearchController extends Controller
{
    public function __invoke(Request $request, AiAccessService $aiAccessService): View
    {
        $facilities = Facility::query()->orderBy('name')->get();
        $recommendationCriteria = null;

        $query = BoardingHouse::query()
            ->published()
            ->with(['primaryPhoto', 'facilities', 'availableRooms']);

        $this->applyManualFilters($query, $request);

        $aiResultToken = $request->string('ai_result')->toString();
        $storedAiResult = $aiResultToken === ''
            ? null
            : $request->session()->get("ai_search_results.{$aiResultToken}");

        if (is_array($storedAiResult)) {
            $recommendationCriteria = $storedAiResult;

            $boardingHouses = $this->recommendationPaginator(
                $query->latest()->get(),
                $recommendationCriteria,
                $request,
            );
        } else {
            $boardingHouses = $query
                ->latest()
                ->paginate(12)
                ->withQueryString();
        }

        return view('boarding-houses.search', [
            'boardingHouses' => $boardingHouses,
            'facilities' => $facilities,
            'filters' => $request->query(),
            'recommendationCriteria' => $recommendationCriteria,
            'aiAccess' => $request->user() === null
                ? null
                : $aiAccessService->status($request->user()),
        ]);
    }

    /**
     * @param  Builder<BoardingHouse>  $query
     */
    private function applyManualFilters(Builder $query, Request $request): void
    {
        $query
            ->when($request->filled('location'), function (Builder $query) use ($request): void {
                $location = $request->string('location')->toString();

                $query->where(function (Builder $query) use ($location): void {
                    $query->where('city', 'like', "%{$location}%")
                        ->orWhere('district', 'like', "%{$location}%")
                        ->orWhere('address', 'like', "%{$location}%");
                });
            })
            ->when($request->filled('type'), fn (Builder $query) => $query->where('type', $request->input('type')))
            ->when($request->filled('price_min'), fn (Builder $query) => $query->where('price_monthly', '>=', $request->integer('price_min')))
            ->when($request->filled('price_max'), fn (Builder $query) => $query->where('price_monthly', '<=', $request->integer('price_max')))
            ->when($request->filled('facilities'), function (Builder $query) use ($request): void {
                foreach ((array) $request->input('facilities', []) as $facilityId) {
                    $query->whereHas('facilities', fn (Builder $query) => $query->whereKey($facilityId));
                }
            });
    }

    /**
     * @param  Collection<int, BoardingHouse>  $boardingHouses
     * @param  array{
     *     location: string|null,
     *     price_max: int|null,
     *     type: string|null,
     *     facility_ids: array<int, int>
     * }  $criteria
     */
    private function recommendationPaginator(Collection $boardingHouses, array $criteria, Request $request): LengthAwarePaginator
    {
        $rankedBoardingHouses = $boardingHouses
            ->map(function (BoardingHouse $boardingHouse) use ($criteria): BoardingHouse {
                $recommendation = $this->recommendationScore($boardingHouse, $criteria);

                $boardingHouse->setAttribute('recommendation_score', $recommendation['score']);
                $boardingHouse->setAttribute('recommendation_breakdown', $recommendation['breakdown']);

                return $boardingHouse;
            })
            ->filter(function (BoardingHouse $boardingHouse) use ($criteria): bool {
                $hasCriteria = $criteria['location'] !== null
                            || $criteria['price_max'] !== null
                            || $criteria['type'] !== null
                            || count($criteria['facility_ids']) > 0;

                if ($hasCriteria && $boardingHouse->getAttribute('recommendation_score') === 0) {
                    return false;
                }

                return true;
            })
            ->sort(function (BoardingHouse $first, BoardingHouse $second): int {
                $scoreComparison = $second->getAttribute('recommendation_score') <=> $first->getAttribute('recommendation_score');

                if ($scoreComparison !== 0) {
                    return $scoreComparison;
                }

                $priceComparison = $first->price_monthly <=> $second->price_monthly;

                if ($priceComparison !== 0) {
                    return $priceComparison;
                }

                return $second->id <=> $first->id;
            })
            ->values();

        $page = max(1, $request->integer('page', 1));
        $perPage = 12;

        return new LengthAwarePaginator(
            $rankedBoardingHouses->forPage($page, $perPage)->values(),
            $rankedBoardingHouses->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ],
        );
    }

    /**
     * @param  array{
     *     location: string|null,
     *     price_max: int|null,
     *     target_latitude?: float|null,
     *     target_longitude?: float|null
     * }  $criteria
     * @return array{score: int, breakdown: array<int, string>}
     *
     * @noinspection PhpConditionAlreadyCheckedInspection
     */
    private function recommendationScore(BoardingHouse $boardingHouse, array $criteria): array
    {
        $score = 0;
        $possibleScore = 0;
        $breakdown = [];

        if ($criteria['location'] !== null) {
            $possibleScore += 30;

            $targetLat = $criteria['target_latitude'] ?? null;
            $targetLng = $criteria['target_longitude'] ?? null;

            if ($targetLat !== null && $targetLng !== null && $boardingHouse->latitude !== null && $boardingHouse->longitude !== null) {
                // Haversine formula
                $earthRadius = 6371; // km
                $latDelta = deg2rad($boardingHouse->latitude - $targetLat);
                $lngDelta = deg2rad($boardingHouse->longitude - $targetLng);

                $a = sin($latDelta / 2) * sin($latDelta / 2) +
                     cos(deg2rad($targetLat)) * cos(deg2rad((float) $boardingHouse->latitude)) *
                     sin($lngDelta / 2) * sin($lngDelta / 2);

                $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
                $distance = $earthRadius * $c;

                if ($distance <= 2) {
                    $score += 30;
                    $breakdown[] = 'Lokasi sangat dekat ('.round($distance, 1).' km)';
                } elseif ($distance <= 5) {
                    $score += 20;
                    $breakdown[] = 'Lokasi cukup dekat ('.round($distance, 1).' km)';
                } elseif ($distance <= 10) {
                    $score += 10;
                    $breakdown[] = 'Lokasi terjangkau ('.round($distance, 1).' km)';
                } else {
                    // Terlalu jauh, fallback text search
                    $this->applyTextLocationScore($boardingHouse, $criteria['location'], $score, $breakdown);
                }
            } else {
                $this->applyTextLocationScore($boardingHouse, $criteria['location'], $score, $breakdown);
            }
        }

        if ($criteria['price_max'] !== null) {
            $possibleScore += 25;

            if ($boardingHouse->price_monthly <= $criteria['price_max']) {
                $score += 25;
                $breakdown[] = 'Sesuai budget';
            }
        }

        if ($criteria['type'] !== null) {
            $possibleScore += 15;

            if ($boardingHouse->type->value === $criteria['type']) {
                $score += 15;
                $breakdown[] = 'Tipe cocok';
            }
        }

        if ($criteria['facility_ids'] !== []) {
            $possibleScore += 30;
            $matchedFacilityIds = $boardingHouse->facilities
                ->pluck('id')
                ->intersect($criteria['facility_ids'])
                ->count();

            if ($matchedFacilityIds > 0) {
                $score += (int) round(30 * ($matchedFacilityIds / count($criteria['facility_ids'])));
                $breakdown[] = 'Fasilitas cocok';
            }
        }

        return [
            'score' => $possibleScore === 0 ? 0 : (int) round(($score / $possibleScore) * 100),
            'breakdown' => $breakdown,
        ];
    }

    private function applyTextLocationScore(BoardingHouse $boardingHouse, string $searchLocation, int &$score, array &$breakdown): void
    {
        $location = Str::squish(Str::lower(Str::ascii($searchLocation)));
        $locationText = Str::squish(Str::lower(Str::ascii(implode(' ', [
            $boardingHouse->name,
            $boardingHouse->city,
            $boardingHouse->district,
            $boardingHouse->address,
            $boardingHouse->description,
        ]))));

        if (Str::contains($locationText, $location)) {
            $score += 30;
            $breakdown[] = 'Lokasi cocok';
        }
    }
}
