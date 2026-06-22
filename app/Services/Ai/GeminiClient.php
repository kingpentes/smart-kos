<?php

namespace App\Services\Ai;

use App\Models\Facility;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GeminiClient
{
    /**
     * @param  array<string, int|float|string|null>  $metrics
     * @return array{
     *     summary: string,
     *     risks: array<int, string>,
     *     recommendations: array<int, string>,
     *     forecast_note: string
     * }|null
     */
    public function generateFinancialInsights(array $metrics): ?array
    {
        $apiKey = config('services.gemini.key');

        if (! is_string($apiKey) || trim($apiKey) === '') {
            return null;
        }

        $model = config('services.gemini.model', 'gemini-2.5-flash');

        try {
            $response = Http::timeout(12)
                ->connectTimeout(5)
                ->withHeaders([
                    'x-goog-api-key' => $apiKey,
                ])
                ->post($this->endpoint($model), [
                    'system_instruction' => [
                        'parts' => [[
                            'text' => 'Anda adalah analis keuangan bisnis kos. Berikan analisis ringkas, berbasis angka, konservatif, dan dapat ditindaklanjuti. Jangan mengarang data di luar metrik yang diberikan.',
                        ]],
                    ],
                    'contents' => [
                        [
                            'parts' => [[
                                'text' => 'Analisis metrik keuangan berikut: '.json_encode($metrics, JSON_THROW_ON_ERROR),
                            ]],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.2,
                        'responseMimeType' => 'application/json',
                        'responseJsonSchema' => [
                            'type' => 'object',
                            'properties' => [
                                'summary' => ['type' => 'string'],
                                'risks' => [
                                    'type' => 'array',
                                    'items' => ['type' => 'string'],
                                ],
                                'recommendations' => [
                                    'type' => 'array',
                                    'items' => ['type' => 'string'],
                                ],
                                'forecast_note' => ['type' => 'string'],
                            ],
                            'required' => ['summary', 'risks', 'recommendations', 'forecast_note'],
                        ],
                    ],
                ]);

            if (! $response->successful()) {
                Log::warning('Gemini financial analysis returned an error.', [
                    'status' => $response->status(),
                ]);

                return null;
            }

            $text = data_get($response->json(), 'candidates.0.content.parts.0.text');
            $decoded = is_string($text) ? json_decode($text, true) : null;

            if (! is_array($decoded)
                || ! is_string($decoded['summary'] ?? null)
                || ! is_array($decoded['risks'] ?? null)
                || ! is_array($decoded['recommendations'] ?? null)
                || ! is_string($decoded['forecast_note'] ?? null)) {
                return null;
            }

            return [
                'summary' => $decoded['summary'],
                'risks' => array_values(array_filter($decoded['risks'], 'is_string')),
                'recommendations' => array_values(array_filter($decoded['recommendations'], 'is_string')),
                'forecast_note' => $decoded['forecast_note'],
            ];
        } catch (ConnectionException $exception) {
            Log::warning('Gemini financial analysis request failed.', [
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

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
     *     source: string
     * }|null
     */
    public function parseBoardingHouseSearchPrompt(string $prompt, Collection $facilities): ?array
    {
        $apiKey = config('services.gemini.key');

        if (! is_string($apiKey) || trim($apiKey) === '') {
            return null;
        }

        $model = config('services.gemini.model', 'gemini-flash-latest');
        $payload = $this->payload($prompt, $facilities);

        try {
            $response = Http::timeout(8)
                ->withHeaders([
                    'x-goog-api-key' => $apiKey,
                ])
                ->post($this->endpoint($model), $payload);

            if ($response->successful()) {
                return $this->criteriaFromResponse($prompt, $facilities, $response->json());
            }

            Log::warning("Gemini search prompt request returned an error for model {$model}.", [
                'status' => $response->status(),
            ]);
        } catch (ConnectionException $exception) {
            Log::warning("Gemini search prompt request failed for model {$model}.", [
                'message' => $exception->getMessage(),
            ]);
        }

        return null;
    }

    private function endpoint(string $model): string
    {
        return 'https://generativelanguage.googleapis.com/v1beta/models/'.$model.':generateContent';
    }

    /**
     * @param  Collection<int, Facility>  $facilities
     * @return array<string, mixed>
     */
    private function payload(string $prompt, Collection $facilities): array
    {
        $facilityNames = $facilities
            ->pluck('name')
            ->values()
            ->implode(', ');

        return [
            'system_instruction' => [
                'parts' => [
                    [
                        'text' => 'Ekstrak kriteria pencarian kos SMART KOST dari prompt pengguna. 
Aturan ekstraksi:
1. location: string atau null. Jika menyebut landmark/kampus/kota.
2. price_max: angka atau null. Jika tidak ada harga spesifik, null.
3. type: "male", "female", atau null. Jika kos bebas/campur/tidak disebut, null.
4. facility_names: array dari nama fasilitas yang diminta.
5. target_latitude: angka atau null. Estimasi garis lintang (latitude) dari landmark/kampus/kota yang disebutkan pada location. (contoh: -1.265386 untuk Balikpapan)
6. target_longitude: angka atau null. Estimasi garis bujur (longitude) dari landmark/kampus/kota yang disebutkan pada location. (contoh: 116.831200 untuk Balikpapan)
PENTING: Selalu coba berikan estimasi latitude dan longitude jika location merujuk pada titik fisik nyata.',
                    ],
                ],
            ],
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => "Prompt pengguna: {$prompt}\nFasilitas tersedia: {$facilityNames}\nTipe kos valid: male, female, mixed.",
                        ],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'responseMimeType' => 'application/json',
                'responseJsonSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'location' => [
                            'type' => 'string',
                            'description' => 'Nama lokasi, area, kampus, kota, kecamatan, atau alamat ringkas yang dicari. Kosongkan jika tidak ada.',
                        ],
                        'price_max' => [
                            'type' => 'integer',
                            'description' => 'Budget maksimal per bulan dalam rupiah. Gunakan 0 jika tidak ada budget.',
                        ],
                        'type' => [
                            'type' => 'string',
                            'enum' => ['', 'male', 'female', 'mixed'],
                            'description' => 'Tipe kos. female untuk putri/perempuan, male untuk putra/laki-laki, mixed untuk campur/bebas. Kosongkan jika tidak ada.',
                        ],
                        'facilities' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'string',
                            ],
                            'description' => 'Nama fasilitas yang diminta pengguna. Gunakan nama dari daftar fasilitas tersedia.',
                        ],
                        'target_latitude' => [
                            'type' => 'number',
                            'description' => 'Estimasi garis lintang (latitude) dari lokasi/landmark. Null jika tidak relevan.',
                        ],
                        'target_longitude' => [
                            'type' => 'number',
                            'description' => 'Estimasi garis bujur (longitude) dari lokasi/landmark. Null jika tidak relevan.',
                        ],
                    ],
                    'required' => ['location', 'price_max', 'type', 'facilities', 'target_latitude', 'target_longitude'],
                ],
            ],
        ];
    }

    /**
     * @param  Collection<int, Facility>  $facilities
     * @param  array<string, mixed>|null  $response
     * @return array{
     *     prompt: string,
     *     normalized_prompt: string,
     *     location: string|null,
     *     price_max: int|null,
     *     type: string|null,
     *     facility_names: array<int, string>,
     *     target_latitude: float|null,
     *     target_longitude: float|null,
     *     source: string
     * }|null
     */
    private function criteriaFromResponse(string $prompt, Collection $facilities, ?array $response): ?array
    {
        $text = data_get($response, 'candidates.0.content.parts.0.text');

        if (! is_string($text) || trim($text) === '') {
            return null;
        }

        $decoded = json_decode($text, true);

        if (! is_array($decoded)) {
            return null;
        }

        $matchedFacilities = $this->matchedFacilities((array) ($decoded['facilities'] ?? []), $facilities);
        $type = is_string($decoded['type'] ?? null) ? $decoded['type'] : null;

        return [
            'prompt' => trim($prompt),
            'normalized_prompt' => $this->normalize($prompt),
            'location' => $this->nullableString($decoded['location'] ?? null),
            'price_max' => $this->nullableInteger($decoded['price_max'] ?? null),
            'type' => in_array($type, ['male', 'female', 'mixed'], true) ? $type : null,
            'facility_ids' => $matchedFacilities['ids'],
            'facility_names' => $matchedFacilities['names'],
            'target_latitude' => is_numeric($decoded['target_latitude'] ?? null) ? (float) $decoded['target_latitude'] : null,
            'target_longitude' => is_numeric($decoded['target_longitude'] ?? null) ? (float) $decoded['target_longitude'] : null,
            'source' => 'gemini',
        ];
    }

    /**
     * @param  array<int, mixed>  $facilityNames
     * @param  Collection<int, Facility>  $facilities
     * @return array{ids: array<int, int>, names: array<int, string>}
     */
    private function matchedFacilities(array $facilityNames, Collection $facilities): array
    {
        $normalizedFacilityNames = collect($facilityNames)
            ->filter(fn (mixed $facilityName): bool => is_string($facilityName))
            ->map(fn (string $facilityName): string => $this->normalize($facilityName));

        $matchedFacilities = $facilities->filter(function (Facility $facility) use ($normalizedFacilityNames): bool {
            $facilityName = $this->normalize($facility->name);
            $facilitySlug = $this->normalize($facility->slug);

            return $normalizedFacilityNames->contains($facilityName)
                || $normalizedFacilityNames->contains($facilitySlug);
        });

        return [
            'ids' => $matchedFacilities->pluck('id')->map(fn (int $id): int => $id)->values()->all(),
            'names' => $matchedFacilities->pluck('name')->values()->all(),
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = Str::squish($value);

        return $value === '' ? null : $value;
    }

    private function nullableInteger(mixed $value): ?int
    {
        if (! is_numeric($value)) {
            return null;
        }

        $value = (int) $value;

        return $value > 0 ? $value : null;
    }

    private function normalize(string $value): string
    {
        return Str::squish(Str::lower(Str::ascii($value)));
    }
}
