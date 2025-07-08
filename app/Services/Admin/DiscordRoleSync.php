<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class DiscordRoleSync
{
    public static function syncRole($role)
    {
        // Get the role's Discord Role ID
        $discordRoleId = $role->discord_role_id;

        // Get all users (accounts) with this role
        $users = DB::table('mship_account_role')
            ->join('mship_account', 'mship_account.id', '=', 'mship_account_role.model_id')
            ->where('mship_account_role.role_id', $role->id)
            ->whereNotNull('mship_account.discord_id')
            ->select('mship_account.discord_id')
            ->get();

        foreach ($users as $user) {
            Http::withToken(config('services.discord.token'))
                ->put("https://discord.com/api/guilds/" . config('services.discord.guild_id') . "/members/{$user->discord_id}/roles/{$discordRoleId}");
        }
    }
}