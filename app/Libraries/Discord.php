<?php

namespace App\Libraries;

use App\Exceptions\Discord\DiscordUserInviteException;
use App\Exceptions\Discord\DiscordUserNotFoundException;
use App\Exceptions\Discord\GenericDiscordException;
use App\Models\Mship\Account;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

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

    public function grantRole(Account $account, string $role): bool
    {
        $role_id = $this->findRole($role);

        return $this->grantRoleById($account, $role_id);
    }

    public function grantRoleById(Account $account, int $role): bool
    {
        $response = Http::withHeaders($this->headers)
            ->put("{$this->base_url}/guilds/{$this->guild_id}/members/{$account->discord_id}/roles/{$role}");

        $retry_after = $response->json()['retry_after'] ?? null;

        // discord API will return a retry field if being rate limited non-globally
        // wait until this has passed before proceeding.
        if ($retry_after) {
            sleep($retry_after);
            $this->grantRoleById($account, $role);
        }

        return $this->result($response);
    }

    public function removeRole(Account $account, string $role): bool
    {
        $role_id = $this->findRole($role);

        return $this->removeRoleById($role_id);
    }

    public function removeRoleById(Account $account, int $role): bool
    {
        $response = Http::withHeaders($this->headers)
            ->delete("{$this->base_url}/guilds/{$this->guild_id}/members/{$account->discord_id}/roles/{$role}");

        return $this->result($response);
    }

    public function setNickname(Account $account, string $nickname): bool
    {
        $response = Http::withHeaders($this->headers)
            ->patch("{$this->base_url}/guilds/{$this->guild_id}/members/{$account->discord_id}",
                [
                    'nick' => $nickname,
                ]
            );

        return $this->result($response);
    }

    public function kick(Account $account): bool
    {
        $response = Http::withHeaders($this->headers)
            ->delete("{$this->base_url}/guilds/{$this->guild_id}/members/{$account->discord_id}");

        if ($response->status() == 404) {
            return true;
        }

        return $this->result($response);
    }

    public function invite(Account $account): bool
    {
        $response = Http::withHeaders($this->headers)
            ->put("{$this->base_url}/guilds/{$this->guild_id}/members/{$account->discord_id}", [
                'access_token' => $account->discord_access_token,
            ]);

        if ($response->status() > 300 && $response->json()['code'] == 30001) {
            throw new DiscordUserInviteException($response, 'You have reached your Discord server limit! You must leave a server before you can join another one');
        }

        return $this->result($response);
    }

    public function getUserRoles(Account $account): Collection
    {
        $response = Http::withHeaders($this->headers)
            ->get("{$this->base_url}/guilds/{$this->guild_id}/members/{$account->discord_id}");

        if (! $response->successful()) {
            return collect([]);
        }

        return collect($response->json()['roles']);
    }

    private function findRole(string $roleName): int
    {
        $response = Http::withHeaders($this->headers)
            ->get("{$this->base_url}/guilds/{$this->guild_id}/roles")->json();

        $role_id = collect($response)
            ->where('name', $roleName)
            ->pluck('id')
            ->first();

        return (int) $role_id;
    }

    public function getUserInformation(Account $account)
    {
        if (! $account->discord_id) {
            return null;
        }

        return Cache::remember($account->id.'.discord.userdata', now()->addHours(12), function () use ($account) {
            return Http::withHeaders($this->headers)
                ->get("{$this->base_url}/users/{$account->discord_id}")->json();
        });
    }

    public function sendMessageToChannel(string $channelId, array $messageContents)
    {
        $response = Http::withHeaders($this->headers)
            ->post("{$this->base_url}/channels/{$channelId}/messages", $messageContents);

        return $this->result($response);
    }

    protected function result(Response $response)
    {
        if ($response->status() == 404 && $response->json()['message'] == 'Unknown Member') {
            throw new DiscordUserNotFoundException($response);
        }

        if ($response->status() > 300) {
            throw new GenericDiscordException($response);
        }

        return true;
    }
}
