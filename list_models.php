<?php

require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Http;

$apiKey = env('GEMINI_API_KEY');

$response = Http::get('https://generativelanguage.googleapis.com/v1beta/models?key='.$apiKey);
if ($response->successful()) {
    $models = $response->json()['models'] ?? [];
    foreach ($models as $m) {
        if (strpos($m['supportedGenerationMethods'][0] ?? '', 'generateContent') !== false) {
            echo $m['name']."\n";
        }
    }
} else {
    echo 'Failed to list models: '.$response->status();
}
