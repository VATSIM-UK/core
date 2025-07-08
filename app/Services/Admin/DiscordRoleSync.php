<?php

namespace App\Services;

use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Http;

class DiscordRoleSync
{
    public static function syncRole(Role $role)
    {
        // Get all users with this role
        $users = $role->users; // Adjust if your relation is different

        foreach ($users as $user) {
            if ($user->discord_id) {
                // Call Discord API to assign the role
                // Replace with your actual Discord role ID mapping logic
                $discordRoleId = self::getDiscordRoleIdForAppRole($role->name);

                Http::withToken(config('services.discord.token'))
                    ->put("https://discord.com/api/guilds/".config('services.discord.guild_id')."/members/{$user->discord_id}/roles/{$role->discord_role_id}");
            }
        }
    }
}