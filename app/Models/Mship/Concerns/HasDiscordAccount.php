<?php

namespace App\Models\Mship\Concerns;

use App\Events\Discord\DiscordUnlinked;
use App\Exceptions\Discord\DiscordUserNotFoundException;
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

        try {
            $discord->setNickname($this, $this->name);
        } catch (DiscordUserNotFoundException $e) {
            return event(new DiscordUnlinked($this));
        }

        $currentRoles = $discord->getUserRoles($this);

        if ($this->isBanned) {
            if ($currentRoles->contains($this->suspendedRoleId)) {
                return;
            }

            $currentRoles->each(function (int $role) use ($discord) {
                $discord->removeRoleById($this, $role);
                sleep(1);
            });

            $discord->grantRoleById($this, $this->suspendedRoleId);
        } else {
            // Grant Roles
            DiscordRole::lazy()->filter(function (DiscordRole $role) {
                return $this->hasPermissionTo($role->permission_id);
            })->each(function (DiscordRole $role) use ($currentRoles, $discord) {
                if (! $currentRoles->contains($role->discord_id)) {
                    $discord->grantRoleById($this, $role->discord_id);
                    sleep(1);
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
                    sleep(1);
                }
            });
        }

        sleep(1);
    }
}
