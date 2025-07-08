<?php

namespace App\Jobs\Adm;

use App\Services\Admin\DiscordRoleSync;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class DiscordRoleSyncJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $roleId;

    public function __construct($roleId)
    {
        $this->roleId = $roleId;
    }

    public function handle()
    {
        $role = DB::table('mship_role')->where('id', $this->roleId)->first();
        if (! $role) {
            DiscordRoleSync::clearSyncFlag($this->roleId);

            return;
        }

        $users = DB::table('mship_account_role')
            ->join('mship_account', 'mship_account.id', '=', 'mship_account_role.model_id')
            ->where('mship_account_role.role_id', $role->id)
            ->whereNotNull('mship_account.discord_id')
            ->select('mship_account.discord_id')
            ->get();

        $webhookUrl = config('services.discord.sync_role_webhook');
        $discordRoleId = $role->discord_role_id;

        // Get cached Discord IDs for this role
        $cacheKey = "discord_synced_role_{$role->id}";
        $alreadySynced = Cache::get($cacheKey, []);

        $userBatches = $users->chunk(10);

        foreach ($userBatches as $batch) {
            foreach ($batch as $user) {
                if (in_array($user->discord_id, $alreadySynced)) {
                    continue; // Skip if already synced
                }
                $response = Http::withToken(config('services.discord.token'))
                    ->put('https://discord.com/api/guilds/'.config('services.discord.guild_id')."/members/{$user->discord_id}/roles/{$discordRoleId}");

                // If successful, add to cache
                if ($response->successful() || $response->status() == 204) {
                    $alreadySynced[] = $user->discord_id;
                }
            }
            // Wait 10 seconds between batches to respect Discord's rate limit
            sleep(10);
        }

        // Update the cache (store for 30 days, adjust as needed)
        Cache::put($cacheKey, $alreadySynced, now()->addDays(30));

        DiscordRoleSync::clearSyncFlag($this->roleId);

        // Send Discord webhook notification
        if ($webhookUrl) {
            $roleName = $role->name ?? "ID {$role->id}";
            $userCount = $users->count();
            $message = [
                'content' => "✅ **Discord role sync complete** for role: `{$roleName}` ({$userCount} members synced).",
            ];
            Http::post($webhookUrl, $message);
        }
    }
}
