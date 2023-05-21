<?php

namespace App\Libraries;

use App\Models\Mship\Account;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
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

    const STAND_STATUS_CACHE_FORMAT = 'UKCP_STAND_STATUS_%s';

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

    public function getStandStatus(string $airfield): array
    {
        try {
            $cached = Cache::get($this->getStandStatusCacheKey($airfield));
            if ($cached) {
                return $cached;
            }

            $response = $this->client->get(
                sprintf('%s/api/stand/status?airfield=%s', config('services.ukcp.url'), $airfield)
            );
            $body = json_decode($response->getBody()->getContents(), true);
            

            return tap(
                collect($body['stands'])->sortBy('identifier', SORT_NUMERIC)->values()->toArray(),
                fn (array $stands) => Cache::put(
                    $this->getStandStatusCacheKey($airfield),
                    collect($stands)->sortBy('identifier', SORT_NUMERIC)->values()->toArray(),
                    Carbon::parse($body['refresh_at'])
                )
            );
        } catch (ClientException $e) {
            Log::warning("UKCP Client Error {$e->getMessage()} when getting stand status for {$airfield}");

            return [];
        }
    }

    private function getStandStatusCacheKey(string $airfield): string
    {
        return sprintf(self::STAND_STATUS_CACHE_FORMAT, $airfield);
    }
}
