<?php

namespace App\Libraries;

use App\Models\Mship\Account;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class Discord
{
    /** @var string */
    private $token;

    /** @var int */
    private $guildId;

    /** @var string */
    private $baseUrl;

    public function __construct()
    {
        $this->token = config('services.discord.token');
        $this->guildId = config('services.discord.guild_id');
        $this->baseUrl = 'https://discordapp.com/api/';
    }

    public function grantRole(Account $account, string $role)
    {
        $roleId = $this->findRole($role);

        $response = Http::withHeaders([
            'Authorization' => "Bot {$this->token}"
        ])->put($this->baseUrl . "/guilds/{$this->guildId}/members/{$account->discord_id}/roles/{$roleId}");

        if ($response->status() > 300) {
            return false;
        }

        return true;
    }

    public function removeRole(Account $account, string $role)
    {
        $roleId = $this->findRole($role);

        $response = Http::withHeaders([
            'Authorization' => "Bot {$this->token}"
        ])->delete($this->baseUrl . "/guilds/{$this->guildId}/members/{$account->discord_id}/roles/{$roleId}");

        return $this->result($response);
    }

    public function setNickname(Account $account, string $nickname)
    {
        $response = Http::withHeaders([
            'Authorization' => "Bot {$this->token}"
        ])->patch($this->baseUrl . "/guilds/{$this->guildId}/members/{$account->discord_id}", [
            'nick' => $nickname
        ]);

        return $this->result($response);
    }

    public function kick(Account $account)
    {
        $response = Http::withHeaders([
            'Authorization' => "Bot {$this->token}"
        ])->delete($this->baseUrl . "/guilds/{$this->guildId}/members/{$account->discord_id}");

        return $this->result($response);
    }

    private function findRole(string $roleName)
    {
        $response = Http::withHeaders([
            'Authorization' => "Bot {$this->token}"
        ])->get($this->baseUrl . "/guilds/{$this->guildId}/roles")->json();

        $roleId = collect($response)
            ->where('name', $roleName)
            ->pluck('id')
            ->first();

        return (int)$roleId;
    }

    protected function result(Response $response)
    {
        if ($response->status() > 300) {
            return false;
        }

        return true;
    }
}
