<?php

use App\Jobs\Adm\DiscordRoleSyncJob;
use Illuminate\Support\Facades\Cache;

class DiscordRoleSync
{
    public static function syncRole($role)
    {
        $syncKey = "discord_role_sync_in_progress_{$role->id}";

        // Prevent duplicate syncs
        if (Cache::get($syncKey)) {
            throw new \Exception('A sync is already in progress for this role.');
        }

        // Get all users (accounts) with this role
        $users = DB::table('mship_account_role')
            ->join('mship_account', 'mship_account.id', '=', 'mship_account_role.account_id')
            ->where('mship_account_role.role_id', $role->id)
            ->whereNotNull('mship_account.discord_id')
            ->select('mship_account.discord_id')
            ->get();

        $threshold = 1000; // Set your API limit here

        if ($users->count() > $threshold) {
            // Mark as in progress
            Cache::put($syncKey, true, now()->addMinutes(30));
            // Dispatch background job
            DiscordRoleSyncJob::dispatch($role->id);

            return 'background';
        }

        // Otherwise, process immediately
        foreach ($users as $user) {
            Http::withToken(config('services.discord.token'))
                ->put('https://discord.com/api/guilds/'.config('services.discord.guild_id')."/members/{$user->discord_id}/roles/{$role->discord_role_id}");
        }

        return 'immediate';
    }

    // Call this after the job finishes
    public static function clearSyncFlag($roleId)
    {
        Cache::forget("discord_role_sync_in_progress_{$roleId}");
    }
}
