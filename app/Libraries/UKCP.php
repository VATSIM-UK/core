<?php

namespace App\Libraries;

use App\Models\Mship\Account;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UKCP
{

    /** @var string */
    private $apiKey;

    /** @var string */
    const TOKEN_PATH_ROOT = 'ukcp/tokens/';

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
            Log::error($e);
            Bugsnag::notifyException($e);
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
     * @return string?
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
            Log::error($e);
            Bugsnag::notifyException($e);
            return null;
        }

        $token = $this->getValidTokensFor($account)->first();

        Storage::disk('local')->put(self::getPathForToken($token->id, $account), $token);
        return $token;
    }

    /**
     * @param string $tokenId
     * @param Account $account
     * @return bool
     */
    public function deleteToken(string $tokenId, Account $account)
    {
        try {
            (new Client)->delete(config('services.ukcp.url') . '/token/' . $tokenId, ['headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey
            ]]);
        } catch (ClientException $e) {
            Log::error($e);
            Bugsnag::notifyException($e);
            return false;
        }

        // Delete local file
        Storage::disk('local')->delete(self::getPathForToken($tokenId, $account));

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
            Log::error($e);
            Bugsnag::notifyException($e);
            return null;
        }

        return json_decode($result->getBody()->getContents());
    }

    /**
     * @param $token object A token object
     * @return false|string
     */
    public static function getKeyForToken($token)
    {
        return substr($token->id, -8);
    }

    /**
     * @param $tokenID string The full length token ID
     * @param $account Account
     * @return string
     */
    public static function getPathForToken($tokenID, $account)
    {
        return self::TOKEN_PATH_ROOT . $account->id . '/' . $tokenID . '.json';
    }
}
