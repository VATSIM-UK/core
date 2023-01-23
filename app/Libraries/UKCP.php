<?php

namespace App\Libraries;

use App\Models\Mship\Account;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Psr\Http\Message\ResponseInterface;

class UKCP
{
    /** @var string */
    private $apiKey;

    /** @var Client */
    private $client;

    /** @var string */
    const TOKEN_PATH_ROOT = 'ukcp/tokens/';

    /**
     * UKCP constructor.
     */
    public function __construct(Client $client)
    {
        $this->apiKey = config('services.ukcp.key');
        $this->client = $client;
    }

    public function createAccountFor(Account $account)
    {
        try {
            $result = $this->client->post(config('services.ukcp.url').'/api/user/'.$account->id, ['headers' => [
                'Authorization' => 'Bearer '.$this->apiKey,
            ]]);
        } catch (ClientException $e) {
            Log::warning("UKCP Client Error {$e->getMessage()} when creating account {$account->id}");

            return;
        }

        return $result->getBody()->getContents();
    }

    /**
     * @param  Account  $account
     * @return array|Collection|mixed|ResponseInterface
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
     * @param  Account  $account
     * @return object|null
     */
    public function createTokenFor(Account $account)
    {
        $pluginAccount = collect($this->getAccountFor($account));

        if ($pluginAccount->isEmpty()) {
            $result = $this->createAccountFor($account);
        } else {
            try {
                $response = $this->client->post(config('services.ukcp.url').'/api/user/'.$account->id.'/token', ['headers' => [
                    'Authorization' => 'Bearer '.$this->apiKey,
                ]]);
                $result = $response->getBody()->getContents();
            } catch (ClientException $e) {
                Log::warning("UKCP Client Error {$e->getMessage()} failed to create UKCP Token for {$account->id}");

                return;
            }
        }

        $token = $this->getValidTokensFor($account)->first();
        Storage::disk('local')->put(self::getPathForToken($token->id, $account), $result);

        return $token;
    }

    /**
     * @param  string  $tokenId
     * @param  Account  $account
     * @return bool
     */
    public function deleteToken(string $tokenId, Account $account)
    {
        try {
            $this->client->delete(config('services.ukcp.url').'/api/token/'.$tokenId, ['headers' => [
                'Authorization' => 'Bearer '.$this->apiKey,
            ]]);
        } catch (ClientException $e) {
            Log::info("UKCP Client Exception $e when getting user account {$account->id}");

            return false;
        }

        // Delete local file
        Storage::disk('local')->delete(self::getPathForToken($tokenId, $account));

        return true;
    }

    protected function getAccountFor(Account $account)
    {
        try {
            $result = $this->client->get(config('services.ukcp.url').'/api/user/'.$account->id, ['headers' => [
                'Authorization' => 'Bearer '.$this->apiKey,
            ]]);
        } catch (ClientException $e) {
            Log::info("UKCP Client Exception {$e->getMessage()} when getting user account {$account->id}");

            return;
        }

        return json_decode($result->getBody()->getContents());
    }

    /**
     * @param $token object|string A token object or token ID string
     * @return false|string
     */
    public static function getKeyForToken($token)
    {
        return substr(is_object($token) ? $token->id : $token, -8);
    }

    /**
     * @param $tokenID string The full length token ID
     * @param $account Account
     * @return string
     */
    public static function getPathForToken($tokenID, $account)
    {
        return self::TOKEN_PATH_ROOT.$account->id.'/'.$tokenID.'.json';
    }
}
