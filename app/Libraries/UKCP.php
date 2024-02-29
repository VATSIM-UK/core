<?php

namespace App\Libraries;

use App\Models\Mship\Account;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
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
     * @param  $token  object|string A token object or token ID string
     * @return false|string
     */
    public static function getKeyForToken($token)
    {
        return substr(is_object($token) ? $token->id : $token, -8);
    }

    /**
     * @param  $tokenID  string The full length token ID
     * @param  $account  Account
     * @return string
     */
    public static function getPathForToken($tokenID, $account)
    {
        return self::TOKEN_PATH_ROOT.$account->id.'/'.$tokenID.'.json';
    }

    public function getStandStatus(string $airfield): array
    {
        try {
            return Cache::remember(
                $this->getStandStatusCacheKey($airfield),
                fn (array $cachedResponse) => $cachedResponse['refresh_at'],
                function () use ($airfield) {
                    $response = $this->client->get(
                        sprintf('%s/api/stand/status?airfield=%s', config('services.ukcp.url'), $airfield),
                        ['timeout' => 8]
                    );
                    $body = json_decode($response->getBody()->getContents(), true);

                    return [
                        'stands' => collect($body['stands'])->sortBy('identifier', SORT_NUMERIC)->values()->toArray(),
                        'refresh_at' => Carbon::parse($body['refresh_at']),
                    ];
                })['stands'];
        } catch (ClientException $e) {
            Log::warning("UKCP Client Error {$e->getMessage()} when getting stand status for {$airfield}");

            return [];
        }
    }

    public function getUnreadNotificationsForUser(Account $account)
    {
        try {
            $url = config('services.ukcp.url')."/api/user/{$account->id}/notifications/unread";
            $result = $this->client->get($url, [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->apiKey,
                ],
            ]);

            return json_decode($result->getBody()->getContents(), true);
        } catch (ClientException $e) {
            Log::info("UKCP Client Exception {$e->getMessage()} when getting notifications");

            return [];
        }
    }

    public function markNotificationReadForUser(Account $account, int $notificationId)
    {
        try {
            $url = config('services.ukcp.url')."/api/user/{$account->id}/notifications/read/{$notificationId}";
            $this->client->put($url, [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->apiKey,
                ],
            ]);

            return true;
        } catch (ClientException $e) {
            dd($e);
            Log::info("UKCP Client Exception {$e->getMessage()} when marking notification read");

            return [];
        }
    }

    private function getStandStatusCacheKey(string $airfieldIcao): string
    {
        return sprintf('UKCP_STAND_STATUS_%s', $airfieldIcao);
    }
}
