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

    public function ratings()
    {
        $url = $this->baseUrl . 'ratings';

        try {
            $result = $this->client->get($url, ['headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey
            ]]);
        } catch (ClientException $e) {
            return null;
        }

        return $result;
    }

    public function ratingsFor(Account $account)
    {
        $url = $this->baseUrl . 'ratings/' . $account->id;

        try {
            $result = $this->client->get($url, ['headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey
            ]]);
        } catch (ClientException $e) {
            return null;
        }

        return $result;
    }
}
