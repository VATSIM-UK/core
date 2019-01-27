<?php

namespace App\Http\Controllers\Site;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\TransferException;
use Illuminate\Support\Facades\Cache;

class MetarController
{
    public function get($airportIcao)
    {
        return Cache::remember("vatsim.metar.$airportIcao", 5, function () use ($airportIcao) {
            $client = new \GuzzleHttp\Client();

            try {
                $response = $client->get("http://metar.vatsim.net/metar.php?id=$airportIcao");

                if ($response->getStatusCode() === 200) {
                    return (string) $response->getBody();
                }
            } catch (\Exception $e) {
                if (!$e instanceof TransferException || !$e instanceof ConnectException) {
                    throw $e;
                }
            }

            return 'METAR UNAVAILABLE';
        });
    }
}
