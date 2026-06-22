<?php

namespace App\Services;

use App\Models\BoardingHouse;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiReviewService
{
    /**
     * Generate an AI review for a boarding house area.
     */
    public function generateAreaReview(BoardingHouse $boardingHouse): array
    {
        $apiKey = config('services.gemini.key');

        $models = [
            config('services.gemini.model', 'gemini-2.5-flash'),
            'gemini-flash-latest',
            'gemini-2.5-pro',
            'gemini-2.0-flash',
        ];

        $prompt = "Tugas Anda adalah meninjau area berdasarkan letak geografis (titik koordinat).
Latitude: {$boardingHouse->latitude}, Longitude: {$boardingHouse->longitude}.
Abaikan nama alamat jika tidak relevan dengan koordinat.
Berikan estimasi jumlah fasilitas umum di radius 1km (Warung Makan, Minimarket, Transportasi Umum, Fasilitas Kesehatan).
Berikan Skor Area dari 1-100 (contoh: 85) yang merepresentasikan kelayakan untuk mahasiswa/pekerja.
Berikan ulasan singkat (maksimal 3 kalimat) mengenai area tersebut.

ANDA WAJIB MENGEMBALIKAN HANYA OBJEK JSON MURNI TANPA BACKTICKS (```json) SEPERTI INI:
{
    \"review\": \"Teks ulasan...\",
    \"score\": 85,
    \"amenities\": {
        \"Warung Makan\": 12,
        \"Minimarket\": 4,
        \"Transportasi Umum\": 5,
        \"Fasilitas Kesehatan\": 2
    }
}";

        if ($apiKey) {
            foreach (array_unique($models) as $model) {
                $result = $this->callGeminiApi($prompt, $apiKey, $model);
                if ($result !== null) {
                    return [
                        ...$result,
                        'source' => 'gemini',
                    ];
                }
            }
        }

        return $this->getFallbackMessage();
    }

    private function callGeminiApi(string $prompt, string $apiKey, string $model): ?array
    {
        try {
            $response = Http::timeout(12)
                ->connectTimeout(5)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=".$apiKey, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

                if ($text) {
                    $text = str_replace(['```json', '```'], '', $text);
                    $decoded = json_decode(trim($text), true);

                    if (json_last_error() === JSON_ERROR_NONE && isset($decoded['review'], $decoded['score'], $decoded['amenities'])) {
                        return $decoded;
                    }
                }
            }
        } catch (ConnectionException $exception) {
            Log::warning("Gemini area review request failed for model {$model}.", [
                'message' => $exception->getMessage(),
            ]);
        }

        return null;
    }

    private function getFallbackMessage(): array
    {
        return [
            'review' => 'Maaf, sistem AI kami sedang tidak dapat memproses ulasan wilayah saat ini. Pastikan konfigurasi AI Anda sudah benar, atau area ini merupakan lokasi kos yang cukup strategis.',
            'score' => 0,
            'amenities' => [
                'Warung Makan' => 0,
                'Minimarket' => 0,
                'Transportasi Umum' => 0,
                'Fasilitas Kesehatan' => 0,
            ],
            'source' => 'fallback',
        ];
    }
}
