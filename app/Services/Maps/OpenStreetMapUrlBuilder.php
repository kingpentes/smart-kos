<?php

namespace App\Services\Maps;

class OpenStreetMapUrlBuilder
{
    /**
     * @return array{embed_url: string, route_url: string, poi_links: array<int, array{label: string, url: string}>}
     */
    public function forCoordinates(float $latitude, float $longitude): array
    {
        return [
            'embed_url' => $this->embedUrl($latitude, $longitude),
            'route_url' => $this->routeUrl($latitude, $longitude),
            'poi_links' => $this->poiLinks($latitude, $longitude),
        ];
    }

    private function embedUrl(float $latitude, float $longitude): string
    {
        $bboxOffset = 0.0035;
        $query = http_build_query([
            'bbox' => implode(',', [
                $this->coordinate($longitude - $bboxOffset),
                $this->coordinate($latitude - $bboxOffset),
                $this->coordinate($longitude + $bboxOffset),
                $this->coordinate($latitude + $bboxOffset),
            ]),
            'layer' => 'mapnik',
            'marker' => $this->coordinate($latitude).','.$this->coordinate($longitude),
        ]);

        // OpenStreetMap embed API requires commas to not be encoded as %2C
        $query = str_replace('%2C', ',', $query);

        return 'https://www.openstreetmap.org/export/embed.html?'.$query;
    }

    private function routeUrl(float $latitude, float $longitude): string
    {
        return sprintf(
            'https://www.openstreetmap.org/?mlat=%s&mlon=%s#map=17/%s/%s',
            $this->coordinate($latitude),
            $this->coordinate($longitude),
            $this->coordinate($latitude),
            $this->coordinate($longitude),
        );
    }

    /**
     * @return array<int, array{label: string, url: string}>
     */
    private function poiLinks(float $latitude, float $longitude): array
    {
        return collect([
            'Minimarket',
            'Kampus',
            'Klinik',
            'ATM',
            'Tempat makan',
        ])->map(fn (string $label): array => [
            'label' => $label,
            'url' => $this->searchUrl($label, $latitude, $longitude),
        ])->values()->all();
    }

    private function searchUrl(string $query, float $latitude, float $longitude): string
    {
        return sprintf(
            'https://www.openstreetmap.org/search?%s#map=16/%s/%s',
            http_build_query(['query' => $query.' dekat '.$this->coordinate($latitude).','.$this->coordinate($longitude)]),
            $this->coordinate($latitude),
            $this->coordinate($longitude),
        );
    }

    private function coordinate(float $coordinate): string
    {
        return number_format($coordinate, 7, '.', '');
    }
}
