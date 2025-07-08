<?php

namespace App\Jobs;

use App\Services\Admin\DiscordRoleSync;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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
            ->join('mship_account', 'mship_account.id', '=', 'mship_account_role.account_id')
            ->where('mship_account_role.role_id', $role->id)
            ->whereNotNull('mship_account.discord_id')
            ->select('mship_account.discord_id')
            ->get();

        foreach ($users as $user) {
            Http::withToken(config('services.discord.token'))
                ->put('https://discord.com/api/guilds/'.config('services.discord.guild_id')."/members/{$user->discord_id}/roles/{$role->discord_role_id}");
        }

        DiscordRoleSync::clearSyncFlag($this->roleId);
        // Send Discord webhook notification
        $webhookUrl = config('services.discord.sync_role_webhook');
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
