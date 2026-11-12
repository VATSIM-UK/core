<?php

namespace App\Http\Controllers\Site;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class MetarController
{
    public function get(string $airportIcao)
    {
        $metar = Cache::remember("vatsim.metar.$airportIcao", 5 * 60, function () use ($airportIcao) {
            $response = Http::withUserAgent('VATSIM-UK')->get('https://metar.vatsim.net/metar.php', ['id' => $airportIcao]);

            if ($response->failed()) {
                return 'METAR UNAVAILABLE';
            }

            return $response->body();
        });

        return response($metar, 200, ['Content-Type' => 'text/plain']);
    }
}
