<?php

namespace App\Services\Site;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class MetarService
{
    public function get(string $airportIcao): string
    {
        return Cache::remember("vatsim.metar.$airportIcao", 5 * 60, function () use ($airportIcao) {
            $response = Http::get("http://metar.vatsim.net/metar.php?id=$airportIcao");

            if ($response->failed()) {
                return $this->unavailableResponse();
            }

            return $response->body();
        });
    }

    private function unavailableResponse(): string
    {
        return 'METAR UNAVAILABLE';
    }
}
