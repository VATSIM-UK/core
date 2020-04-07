<?php

namespace App\Libraries;

use App\Models\Mship\Account;
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
        $this->baseUrl = config('vatsim-api.base');
        $this->apiKey = config('vatsim-api.key');
        $this->client = $client;
    }

    /*
     * Returns a list of users that are in your region and division.
     */
    public function ratings()
    {
        $url = $this->baseUrl . 'ratings';

        $result = $this->client->get($url, ['headers' => [
            'Authorization' => 'Token ' . $this->apiKey
        ]]);

        return $result;
    }

    /*
     * Returns a set of data for a specific user..
     */
    public function ratingsFor(Account $account)
    {
        $url = $this->baseUrl . 'ratings/' . $account->id;

        $result = $this->client->get($url, ['headers' => [
            'Authorization' => 'Token ' . $this->apiKey
        ]]);

        return $result;
    }
}
