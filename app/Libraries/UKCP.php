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
        $this->apiKey = config('services.ukcp.key');
    }

    /**
     * @param Account $account
     * @return array|\Illuminate\Support\Collection|mixed|\Psr\Http\Message\ResponseInterface
     */
    public function getValidTokens(Account $account)
    {
        try {
            $client = new Client;
            $result = $client->get(config('services.ukcp.url') . '/user/' . $account->id, ['headers' => [
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

    /**
     * @param string $tokenId
     * @return bool
     */
    public function deleteToken(string $tokenId)
    {
        try {
            (new Client)->delete(config('services.ukcp.url') . $tokenId, ['headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey
            ]]);
        } catch (ClientException $e) {
            return false;
        }

        return true;
    }
}
