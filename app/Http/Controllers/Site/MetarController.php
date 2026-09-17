<?php

namespace App\Http\Controllers\Site;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class MetarController
{
    public function get(Request $request, $airportIcao)
    {
        $metar = Cache::remember("vatsim.metar.$airportIcao", 5 * 60, function () use ($airportIcao) {
            $response = Http::withUserAgent('VATSIM-UK')->get("http://metar.vatsim.net/metar.php?id=$airportIcao");

            if ($response->failed()) {
                return 'METAR UNAVAILABLE';
            }

            return $response->body();
        });

        // Defaults to html unless the client's Accept header prefers plain text.
        $contentType = $request->prefers(['text/html', 'text/plain']) ?? 'text/html';

        return response($metar, 200, ['Content-Type' => $contentType]);
    }
}
