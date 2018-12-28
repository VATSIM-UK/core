<?php

namespace App\Libraries;

use App\Models\Mship\Account;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class UKCP
{

    /** @var string */
    private $apiKey;

    /**
     * UKCP constructor.
     */
    public function __construct()
    {
        $this->apiKey = env('UKCP_KEY');
    }

    /**
     * @param Account $account
     * @return array|\Illuminate\Support\Collection|mixed|\Psr\Http\Message\ResponseInterface
     */
    public function getValidTokens(Account $account)
    {
        try {
            $client = new Client;
            $result = $client->get(env('UKCP_BASE_URL') . '/user/' . $account->id, ['headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey
            ]]);
        } catch (ClientException $e) {
            return collect();
        }

        $result = json_decode($result->getBody()->getContents());
        $result = collect($result->tokens)->filter(function ($item) {
            return $item->revoked === false;
        })->all();

        return $result;
    }
}
