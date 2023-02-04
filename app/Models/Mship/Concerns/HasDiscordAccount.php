<?php

namespace App\Models\Mship\Concerns;

use App\Events\Discord\DiscordUnlinked;
use App\Exceptions\Discord\DiscordUserNotFoundException;
use App\Libraries\Discord;
use App\Models\Discord\DiscordQualificationRole;
use App\Models\Discord\DiscordRole;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Trait HasDiscordAccount.
 */
trait HasDiscordAccount
{
    public function getDiscordNameAttribute()
    {
        if (Str::length($this->name) >= 32) {
            return $this->name_preferred . ' ' . substr($this->name_last, 0, 1);
        }

        return $this->name;
    }

    /**
     * Sync the current account to Discord.
     */
    public function syncToDiscord()
    {
        if (!config('services.discord.token')) {
            return;
        }

        $discord = (new Discord);

        $suspendedRoleId = config('services.discord.suspended_member_role_id');

        // Attempt to set user's nickname
        try {
            $discord->setNickname($this, $this->discordName);
        } catch (DiscordUserNotFoundException $e) {
            return event(new DiscordUnlinked($this));
        }

        // Retrieve users current roles
        $currentRoles = $discord->getUserRoles($this);

        // Handle if the user is banned on core
        if ($this->isBanned) {

            // If they are already in the suspended role, we are happy
            if ($currentRoles->contains($suspendedRoleId)) return;

            // Remove each of their current roles
            $currentRoles->each(function (int $role) use ($discord) {
                $discord->removeRoleById($this, $role);
                sleep(1);
            });

            // Give them the suspended user role
            $discord->grantRoleById($this, $suspendedRoleId);

            // We'll return, as suspended users should only have this suspended role
            return;
        }

        // If they have the suspended role, remove it (no longer suspended)
        if ($currentRoles->contains($suspendedRoleId)) $discord->removeRoleById($this, $suspendedRoleId);

        // Evaluate available discord 
        $discordRoles = DiscordRole::lazy()->groupBy(fn (DiscordRole $role) => $this->hasPermissionTo($role->permission_id));
        $discordQualificationRoles = DiscordQualificationRole::lazy()->groupBy(fn (DiscordQualificationRole $role) => $role->accountSatisfies($this));

        $rolesShouldHave = array_merge(
            $discordRoles->get('true', []),
            $discordQualificationRoles->get('true', [])
        );

        $rolesShouldNotHave = array_merge(
            $discordRoles->get('false', []),
            $discordQualificationRoles->get('false', [])
        );

        //     $rolesShouldHaveByPermission = DiscordRole::lazy()->filter(function (DiscordRole $role) {
        //         return $this->hasPermissionTo($role->permission_id);
        //     })->map(fn (DiscordRole $role) => $role->discord_id);

        // $rolesShouldHaveByQualification = DiscordQualificationRole::lazy()->filter(function (DiscordRole $role) {
        //     return $this->hasPermissionTo($role->permission_id);
        // })->map(fn (DiscordRole $role) => $role->discord_id);



        // // Grant roles allowed by permissions
        // DiscordRole::lazy()->filter(function (DiscordRole $role) {
        //     return $this->hasPermissionTo($role->permission_id);
        // })->each(function (DiscordRole $role) use ($currentRoles, $discord) {
        //     if (!$currentRoles->contains($role->discord_id)) {
        //         $discord->grantRoleById($this, $role->discord_id);
        //         sleep(1);
        //     }
        // });


        // // For each role they don't have permission to, remove the role
        // DiscordRole::lazy()->filter(function (DiscordRole $role) {
        //     return !$this->hasPermissionTo($role->permission_id);
        // })->each(function (DiscordRole $role) use ($currentRoles, $discord) {
        //     if ($currentRoles->contains($role->discord_id)) {
        //         $discord->removeRoleById($this, $role->discord_id);
        //         sleep(1);
        //     }
        // });

        // sleep(1);
    }
}
