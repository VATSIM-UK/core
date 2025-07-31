<?php

namespace App\Libraries;

use App\Exceptions\Discord\DiscordUserInviteException;
use App\Exceptions\Discord\DiscordUserNotFoundException;
use App\Exceptions\Discord\GenericDiscordException;
use App\Models\Mship\Account;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Discord
{
    /** @var string */
    private $token;

    /** @var int */
    private $guild_id;

    /** @var string */
    private $base_url;

    /** @var array */
    private $headers;

    public function __construct()
    {
        $this->token = config('services.discord.token');
        $this->guild_id = config('services.discord.guild_id');
        $this->base_url = config('services.discord.base_discord_uri');
        $this->headers = ['Authorization' => "Bot {$this->token}"];
    }

    // --- Public API ---

    public function grantRole(Account $account, string $role): bool
    {
        $role_id = $this->findRole($role);

        return $this->grantRoleById($account, $role_id);
    }

    public function grantRoleById(Account $account, int $role): bool
    {
        $endpoint = "{$this->base_url}/guilds/{$this->guild_id}/members/{$account->discord_id}/roles/{$role}";
        $context = [
            'action' => 'grantRoleById',
            'account_id' => $account->id,
            'discord_id' => $account->discord_id,
            'role_id' => $role,
        ];

        $response = $this->rateLimitedRequest(
            fn () => Http::withHeaders($this->headers)->put($endpoint),
            $context
        );

        return $this->result($response, $context);
    }

    public function removeRole(Account $account, string $role): bool
    {
        $role_id = $this->findRole($role);

        return $this->removeRoleById($account, $role_id);
    }

    public function removeRoleById(Account $account, int $role): bool
    {
        $endpoint = "{$this->base_url}/guilds/{$this->guild_id}/members/{$account->discord_id}/roles/{$role}";
        $context = [
            'action' => 'removeRoleById',
            'account_id' => $account->id,
            'discord_id' => $account->discord_id,
            'role_id' => $role,
        ];

        $response = $this->rateLimitedRequest(
            fn () => Http::withHeaders($this->headers)->delete($endpoint),
            $context
        );

        return $this->result($response, $context);
    }

    public function setNickname(Account $account, string $nickname): bool
    {
        $endpoint = "{$this->base_url}/guilds/{$this->guild_id}/members/{$account->discord_id}";
        $context = [
            'action' => 'setNickname',
            'account_id' => $account->id,
            'discord_id' => $account->discord_id,
            'nickname' => $nickname,
        ];

        $response = $this->rateLimitedRequest(
            fn () => Http::withHeaders($this->headers)->patch($endpoint, ['nick' => $nickname]),
            $context
        );

        return $this->result($response, $context);
    }

    public function kick(Account $account): bool
    {
        $endpoint = "{$this->base_url}/guilds/{$this->guild_id}/members/{$account->discord_id}";
        $context = [
            'action' => 'kick',
            'account_id' => $account->id,
            'discord_id' => $account->discord_id,
        ];

        $response = $this->rateLimitedRequest(
            fn () => Http::withHeaders($this->headers)->delete($endpoint),
            $context
        );

        if ($response->status() == 404) {
            Log::info('Discord kick: user not found, treating as success', $context);

            return true;
        }

        return $this->result($response, $context);
    }

    public function invite(Account $account): bool
    {
        $endpoint = "{$this->base_url}/guilds/{$this->guild_id}/members/{$account->discord_id}";
        $context = [
            'action' => 'invite',
            'account_id' => $account->id,
            'discord_id' => $account->discord_id,
        ];

        $response = $this->rateLimitedRequest(
            fn () => Http::withHeaders($this->headers)->put($endpoint, [
                'access_token' => $account->discord_access_token,
            ]),
            $context
        );

        if ($response->status() > 300 && $response->json()['code'] == 30001) {
            Log::warning('Discord invite: server limit reached', $context);
            throw new DiscordUserInviteException($response, 'You have reached your Discord server limit! You must leave a server before you can join another one');
        }

        return $this->result($response, $context);
    }

    public function getUserRoles(Account $account): Collection
    {
        $endpoint = "{$this->base_url}/guilds/{$this->guild_id}/members/{$account->discord_id}";
        $context = [
            'action' => 'getUserRoles',
            'account_id' => $account->id,
            'discord_id' => $account->discord_id,
        ];

        $response = $this->rateLimitedRequest(
            fn () => Http::withHeaders($this->headers)->get($endpoint),
            $context
        );

        if (! $response->successful()) {
            Log::warning('Discord getUserRoles: failed', $context + ['status' => $response->status()]);

            return collect([]);
        }

        return collect($response->json()['roles']);
    }

    public function getUserInformation(Account $account)
    {
        if (! $account->discord_id) {
            return null;
        }

        return Cache::remember($account->id.'.discord.userdata', now()->addHours(12), function () use ($account) {
            $endpoint = "{$this->base_url}/users/{$account->discord_id}";
            $context = [
                'action' => 'getUserInformation',
                'account_id' => $account->id,
                'discord_id' => $account->discord_id,
            ];
            $response = $this->rateLimitedRequest(
                fn () => Http::withHeaders($this->headers)->get($endpoint),
                $context
            );

            return $response->json();
        });
    }

    public function sendMessageToChannel(string $channelId, array $messageContents)
    {
        $endpoint = "{$this->base_url}/channels/{$channelId}/messages";
        $context = [
            'action' => 'sendMessageToChannel',
            'channel_id' => $channelId,
        ];

        $response = $this->rateLimitedRequest(
            fn () => Http::withHeaders($this->headers)->post($endpoint, $messageContents),
            $context
        );

        return $this->result($response, $context);
    }

    // --- Internal helpers ---

    /**
     * Handles Discord rate limits, logs, and fires events for observability.
     */
    protected function rateLimitedRequest(callable $requestCallback, array $context = [], int $maxAttempts = 5): Response
    {
        $attempt = 0;
        do {
            $response = $requestCallback();
            $retry_after = $response->json()['retry_after'] ?? null;

            if ($retry_after) {
                $context['retry_after'] = $retry_after;
                $context['attempt'] = $attempt + 1;
                Log::warning('Discord rate limit hit', $context);
                Event::dispatch('discord.rate_limited', [$context, $response]);
                sleep((int) ceil($retry_after));
            }

            $attempt++;
        } while ($retry_after && $attempt < $maxAttempts);

        $context['attempts'] = $attempt;
        $context['status'] = $response->status();

        if ($response->failed()) {
            Log::error('Discord API call failed', $context + ['body' => $response->json()]);
            Event::dispatch('discord.api_failed', [$context, $response]);
        } else {
            Log::info('Discord API call succeeded', $context);
            Event::dispatch('discord.api_succeeded', [$context, $response]);
        }

        return $response;
    }

    private function findRole(string $roleName): int
    {
        $endpoint = "{$this->base_url}/guilds/{$this->guild_id}/roles";
        $context = [
            'action' => 'findRole',
            'role_name' => $roleName,
        ];

        $response = $this->rateLimitedRequest(
            fn () => Http::withHeaders($this->headers)->get($endpoint),
            $context
        )->json();

        $role_id = collect($response)
            ->where('name', $roleName)
            ->pluck('id')
            ->first();

        return (int) $role_id;
    }

    /**
     * Handles Discord API response, throws exceptions, and logs as needed.
     */
    protected function result(Response $response, array $context = [])
    {
        if ($response->status() == 404 && ($response->json()['message'] ?? '') == 'Unknown Member') {
            Log::notice('Discord user not found', $context);
            Event::dispatch('discord.user_not_found', [$context, $response]);
            throw new DiscordUserNotFoundException($response);
        }

        if ($response->status() > 300) {
            Log::error('Discord API error', $context + ['body' => $response->json()]);
            Event::dispatch('discord.api_error', [$context, $response]);
            throw new GenericDiscordException($response);
        }

        return true;
    }
}
