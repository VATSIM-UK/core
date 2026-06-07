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
use Illuminate\Support\Facades\Redis;

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

    /**
     * Per-route rate limit bucket tracking (keyed by X-RateLimit-Bucket).
     *
     * @var array<string, array{remaining: int, reset_at: float}>
     */
    private array $rateLimitBuckets = [];

    /** @var int Consecutive 429 responses for exponential backoff */
    private int $consecutive429s = 0;

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

    public function setRoles(Account $account, array $roleIds): bool
    {
        $endpoint = "{$this->base_url}/guilds/{$this->guild_id}/members/{$account->discord_id}";
        $context = [
            'action' => 'setRoles',
            'account_id' => $account->id,
            'discord_id' => $account->discord_id,
            'role_ids' => $roleIds,
        ];

        $response = $this->rateLimitedRequest(
            fn () => Http::withHeaders($this->headers)->patch($endpoint, ['roles' => $roleIds]),
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

        if ($response->status() > 300 && $response->json('code') == 30001) {
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

        return collect($response->json('roles', []));
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

        return $response->json();
    }

    public function createThreadFromMessage(string $channelId, string $messageId, array $data)
    {
        $endpoint = "{$this->base_url}/channels/{$channelId}/messages/{$messageId}/threads";
        $context = [
            'action' => 'createThreadFromMessage',
            'channel_id' => $channelId,
            'message_id' => $messageId,
            'data' => $data,
        ];

        $response = $this->rateLimitedRequest(
            fn () => Http::withHeaders($this->headers)->post($endpoint, $data),
            $context
        );

        if ($response->failed()) {
            Log::error('Failed to create Discord thread', $context + ['status' => $response->status(), 'body' => $response->json()]);
            throw new GenericDiscordException($response);
        }

        return $response->json();
    }

    /*
     * Temporarily mutes the user and purges their recent messages
     */
    public function softBan(Account $account, int $messageRemovalHours, int $muteDurationDays, string $reason = 'Soft ban'): void
    {
        // time out the user
        $endpoint = "{$this->base_url}/guilds/{$this->guild_id}/members/{$account->discord_id}";
        $context = [
            'action' => 'softBan_timeout',
            'account_id' => $account->id,
            'discord_id' => $account->discord_id,
            'mute_duration_days' => $muteDurationDays,
            'reason' => $reason,
        ];

        $expiresAt = now()->addDays($muteDurationDays)->format('Y-m-d\TH:i:s.uP');

        $response = $this->rateLimitedRequest(
            fn () => Http::withHeaders($this->headers)->patch($endpoint, ['communication_disabled_until' => $expiresAt]),
            $context
        );

        if ($response->failed()) {
            Log::error('Failed to timeout Discord user', $context + ['status' => $response->status(), 'body' => $response->json()]);
            throw new GenericDiscordException($response);
        }

        // delete recent messages
        $messages = $this->getMessagesFromUserInGuild($account->discord_id, $messageRemovalHours);
        $messagesByChannel = collect($messages)->groupBy('channel_id');
        foreach ($messagesByChannel as $channelId => $messages) {
            $messageIds = collect($messages)->pluck('id')->unique()->values()->toArray();
            $this->bulkDeleteMessages($channelId, $messageIds);
        }
    }

    /**
     * Fetch recent messages from a user across all text channels in the guild.
     *
     *
     * @return array List of message objects with at least `id`, `channel_id`, `timestamp`
     */
    public function getMessagesFromUserInGuild(string $userId, int $hours = 6): array
    {
        $cutoff = now()->subHours($hours);
        $found = [];

        // list all channeůs
        $channelsResponse = $this->rateLimitedRequest(
            fn () => Http::withHeaders($this->headers)->get("{$this->base_url}/guilds/{$this->guild_id}/channels"),
            ['action' => 'getGuildChannels', 'user_id' => $userId]
        );

        if ($channelsResponse->failed()) {
            Log::warning('Failed to list guild channels for message purge', ['status' => $channelsResponse->status()]);

            return [];
        }

        // for each text channel, fetch recent messages and filter by author
        $textChannelTypes = [0, 5]; // GUILD_TEXT, GUILD_NEWS
        foreach ($channelsResponse->json() as $channel) {
            if (! in_array($channel['type'] ?? -1, $textChannelTypes, true)) {
                continue;
            }

            $response = $this->rateLimitedRequest(
                fn () => Http::withHeaders($this->headers)->get(
                    "{$this->base_url}/channels/{$channel['id']}/messages",
                    ['limit' => 100]
                ),
                ['action' => 'getChannelMessages', 'channel_id' => $channel['id'], 'user_id' => $userId]
            );

            if ($response->failed()) {
                continue;
            }

            foreach ($response->json() as $message) {
                if (
                    isset($message['author']['id'])
                    && (string) $message['author']['id'] === (string) $userId
                    && isset($message['timestamp'])
                    && \Illuminate\Support\Carbon::parse($message['timestamp'])->greaterThan($cutoff)
                ) {
                    $found[$message['id']] = $message;
                }
            }
        }

        return array_values($found);
    }

    /*
     * Bulk delete messages in a channel by their IDs.
     */
    public function deleteMessage(string $channelId, string $messageId): bool
    {
        $endpoint = "{$this->base_url}/channels/{$channelId}/messages/{$messageId}";
        $context = [
            'action' => 'deleteMessage',
            'channel_id' => $channelId,
            'message_id' => $messageId,
        ];

        $response = $this->rateLimitedRequest(
            fn () => Http::withHeaders($this->headers)->delete($endpoint),
            $context
        );

        return $this->result($response, $context);
    }

    public function bulkDeleteMessages(string $channelId, array $messageIds): bool
    {
        $count = count($messageIds);

        if ($count === 1) {
            return $this->deleteMessage($channelId, $messageIds[0]);
        }

        if ($count > 100) {
            foreach (array_chunk($messageIds, 100) as $chunk) {
                $this->bulkDeleteMessages($channelId, $chunk);
            }

            return true;
        }

        $endpoint = "{$this->base_url}/channels/{$channelId}/messages/bulk-delete";
        $context = [
            'action' => 'bulkDeleteMessages',
            'channel_id' => $channelId,
            'message_ids' => $messageIds,
        ];

        $response = $this->rateLimitedRequest(
            fn () => Http::withHeaders($this->headers)->post($endpoint, ['messages' => $messageIds]),
            $context
        );

        if ($response->failed()) {
            Log::error('Failed to bulk delete Discord messages', $context + ['status' => $response->status(), 'body' => $response->json()]);
            throw new GenericDiscordException($response);
        }

        return true;
    }

    // --- Internal helpers ---

    /**
     * Handles Discord rate limits with proactive header-based pacing,
     * jittered retry backoff, and global rate limit protection.
     *
     * Per Discord docs:
     * - Parse X-RateLimit-* headers from every response to avoid hitting limits
     * - Use retry_after + jitter
     * - Use exponential backoff for repeated 429s
     * - Track global rate limit (50 req/sec) via Redis
     */
    protected function rateLimitedRequest(callable $requestCallback, array $context = [], int $maxAttempts = 5): Response
    {
        $attempt = 0;
        $this->consecutive429s = 0;

        do {
            $this->proactivePreRequestDelay();
            $this->checkGlobalRateLimit();

            $response = $requestCallback();
            $retry_after = $response->json('retry_after');

            if ($retry_after) {
                $this->consecutive429s++;

                $context['retry_after'] = $retry_after;
                $context['attempt'] = $attempt + 1;
                $context['ratelimit_global'] = $response->header('X-RateLimit-Global') === 'true';
                $context['ratelimit_scope'] = $response->header('X-RateLimit-Scope');

                Log::warning('Discord rate limit hit', $context);
                Event::dispatch('discord.rate_limited', [$context, $response]);

                $waitTime = (float) $retry_after;

                // Exponential backoff
                if ($this->consecutive429s > 1) {
                    $backoffMultiplier = min(1.5 ** ($this->consecutive429s - 1), 5.0);
                    $waitTime *= $backoffMultiplier;
                }

                // Jitter to prevent starting at once
                $jitter = mt_rand(0, 1000) / 1000; // 0–1 seconds
                $waitTime += $jitter;

                $waitTime = min($waitTime, 30.0);

                usleep((int) ($waitTime * 1_000_000));
            } else {
                $this->consecutive429s = 0;
                $this->updateBucketTracking($response);
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

    /**
     * Wait before a request if we're close to exhausting a known rate limit bucket.
     */
    protected function proactivePreRequestDelay(): void
    {
        $now = microtime(true);

        foreach ($this->rateLimitBuckets as $bucket => $info) {
            $timeUntilReset = $info['reset_at'] - $now;

            // If we have ≤ 2 requests remaining and the bucket hasn't reset yet
            if ($info['remaining'] <= 2 && $timeUntilReset > 0) {
                $delay = $timeUntilReset + 0.05; // 50ms buffer
                usleep((int) ($delay * 1_000_000));

                return;
            }
        }
    }

    /**
     * Track global rate limit (50 req/sec per bot) across all workers via Redis.
     */
    protected function checkGlobalRateLimit(): void
    {
        try {
            $redis = Redis::connection();
            $currentSecond = time();
            $key = "discord:global_rps:{$currentSecond}";

            $count = $redis->incr($key);
            $redis->expire($key, 2);

            if ($count > 45) {
                // Approaching 50 req/sec — wait until the next second window
                $waitTime = ($currentSecond + 1) - microtime(true) + 0.05;
                if ($waitTime > 0) {
                    usleep((int) ($waitTime * 1_000_000));
                }
            }
        } catch (\Exception $e) {
            Log::notice('Discord global rate limit check unavailable', [
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Parse X-RateLimit-* headers from a successful response and store per-bucket state
     * for proactive pacing on subsequent requests within this sync session.
     */
    protected function updateBucketTracking(Response $response): void
    {
        $bucket = $response->header('X-RateLimit-Bucket');
        if (! $bucket) {
            return;
        }

        $remaining = (int) $response->header('X-RateLimit-Remaining', 0);
        $resetAfter = (float) $response->header('X-RateLimit-Reset-After', 0);

        $this->rateLimitBuckets[$bucket] = [
            'remaining' => $remaining,
            'reset_at' => microtime(true) + $resetAfter,
        ];
    }

    private function findRole(string $roleName)
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

        return $role_id;
    }

    /**
     * Handles Discord API response, throws exceptions, and logs as needed.
     */
    protected function result(Response $response, array $context = [])
    {
        if ($response->status() == 404 && $response->json('message') == 'Unknown Member') {
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
