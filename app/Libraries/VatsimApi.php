<?php

namespace App\Libraries;

use GuzzleHttp\Client;

class VatsimApi
{
    /** @var string */
    private $apiKey;

    /** @var string */
    private $baseUrl;

    /** @var Client */
    private $client;

    /**
     * VATSIM API constructor.
     */
    public function __construct(Client $client)
    {
        $this->baseUrl = config('services.vatsim-api.base');
        $this->apiKey = config('services.vatsim-api.key');
        $this->client = $client;
    }

    /*
     * Returns a set of data for a given user.
     * Replacement for idstatusint
     */
    public function ratingsFor($id)
    {
        $url = $this->baseUrl . '/ratings/' . $id;

        $result = json_decode(
            $result = $this->client->get($url, ['headers' => [
                'Authorization' => 'Token ' . $this->apiKey
            ]])->getBody()
                ->getContents()
        );

        return json_decode(json_encode([
            "name_last" => $result->name_last,
            "name_first" => $result->name_first,
            "email" => '[hidden]', // Retained for backwards compatibility
            "rating" => (string) $result->rating,
            "regdate" => $result->reg_date, // Currently returning datetime in a different format vs AutoTools
            "pilotrating" => (string) $result->pilotrating,
            "country" => $result->country,
            "region" => $result->region,
            "division" => $result->division,
            "atctime" => '0', // Retained for backwards compatibility
            "pilottime" => '0', // Retained for backwards compatibility
            "cid" => $result->id,
        ]));
    }

    /*
     * Returns the previous rating for the given user.
     * Replacement for idstatusprat
     */
    public function previousRatingFor($id)
    {
//        $url = $this->baseUrl . '/????/' . $id;
//
//        $result = json_decode(
//            $result = $this->client->get($url, ['headers' => [
//                'Authorization' => 'Token ' . $this->apiKey
//            ]])->getBody()
//                ->getContents()
//        );

        return json_decode(json_encode([
            "rating" => '',
            "PreviousRating" => '',
            "PreviousRatingInt" => '',
            "cid" => '',
        ]));
    }
}
