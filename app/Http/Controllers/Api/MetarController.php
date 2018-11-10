<?php

namespace App\Http\Controllers\Api;

class MetarController
{
    public function get($airportIcao)
    {
        return Cache::remember("vatsim.metar.$airportIcao", 5, function () use ($airportIcao) {
            $client = new GuzzleHttp\Client();

            try {
                $response = $client->get("http://metar.vatsim.net/metar.php?id=$airportIcao");

                if ($response->getStatusCode() === 200) {
                    return (string) $response->getBody();
                }
            } catch (GuzzleHttp\Exception\TransferException $e) {
            }

            return 'METAR UNAVAILABLE';
        });
    }
}
