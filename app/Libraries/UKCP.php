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

    public function createAccountFor(Account $account)
    {
        try {
            $client = new Client;
            $result = $client->post(config('services.ukcp.url') . '/user/' . $account->id, ['headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey
            ]]);
        } catch (ClientException $e) {
            return null;
        }

        return $result->getBody()->getContents();
    }

    /**
     * @param Account $account
     * @return array|\Illuminate\Support\Collection|mixed|\Psr\Http\Message\ResponseInterface
     */
    public function getValidTokensFor(Account $account)
    {
        $tokens = optional($this->getAccountFor($account))->tokens;

        return collect($tokens)
            ->filter(function ($item) {
                return $item->revoked === false;
            });
    }

    /**
     * @param Account $account
     * @return bool
     */
    public function createTokenFor(Account $account)
    {
        $pluginAccount = collect($this->getAccountFor($account));

        if ($pluginAccount->isEmpty()) {
            return $this->createAccountFor($account);
        }

        try {
            $response = (new Client)->post(config('services.ukcp.url') . '/user/' . $account->id . '/token', ['headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey
            ]]);
            $result = $response->getBody()->getContents();
        } catch (ClientException $e) {
            return null;
        }

        return $result;
    }

    /**
     * @param string $tokenId
     * @return bool
     */
    public function deleteToken(string $tokenId)
    {
        try {
            (new Client)->delete(config('services.ukcp.url') . '/token/' . $tokenId, ['headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey
            ]]);
        } catch (ClientException $e) {
            return false;
        }

        return true;
    }

    protected function getAccountFor(Account $account)
    {
        try {
            $client = new Client;
            $result = $client->get(config('services.ukcp.url') . '/user/' . $account->id, ['headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey
            ]]);
        } catch (ClientException $e) {
            return null;
        }

        return json_decode($result->getBody()->getContents());
    }
}
