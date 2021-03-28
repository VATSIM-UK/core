<?php

namespace App\Models\Mship\Concerns;

use App\Libraries\Discord;
use App\Models\Discord\DiscordRole;

/**
 * Trait HasDiscordAccount.
 */
trait HasDiscordAccount
{
    /**
     * Sync the current account to Discord.
     */
    public function syncToDiscord()
    {
        $discord = (new Discord);

        $suspendedRoleId = config('services.discord.suspended_member_role_id');

        $currentRoles = $discord->getUserRoles($this);

        $discord->setNickname($this, $this->name);

        if ($this->isBanned) {
            if ($currentRoles->contains($this->suspendedRoleId)) {
                return;
            }

            $currentRoles->each(function (int $role) use ($discord) {
                $discord->removeRoleById($this, $role);
            });

            $discord->grantRoleById($this, $this->suspendedRoleId);
        } else {
            // Grant Roles
            DiscordRole::lazy()->filter(function (DiscordRole $role) {
                return $this->hasPermissionTo($role->permission_id);
            })->each(function (DiscordRole $role) use ($currentRoles, $discord) {
                if (! $currentRoles->contains($role->discord_id)) {
                    $discord->grantRoleById($this, $role->discord_id);
                }
            });

            // Remove Roles
            if ($currentRoles->contains($suspendedRoleId)) {
                $discord->removeRoleById($this, $suspendedRoleId);
            }

            DiscordRole::lazy()->filter(function (DiscordRole $role) {
                return ! $this->hasPermissionTo($role->permission_id);
            })->each(function (DiscordRole $role) use ($currentRoles, $discord) {
                if ($currentRoles->contains($role->discord_id)) {
                    $discord->removeRoleById($this, $role->discord_id);
                }
            });
        }
    }
}
