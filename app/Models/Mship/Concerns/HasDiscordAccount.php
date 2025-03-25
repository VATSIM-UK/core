<?php

namespace App\Models\Mship\Concerns;

use App\Events\Discord\DiscordUnlinked;
use App\Exceptions\Discord\DiscordUserNotFoundException;
use App\Libraries\Discord;
use App\Models\Discord\DiscordRoleRule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Trait HasDiscordAccount.
 */
trait HasDiscordAccount
{
    public function getDiscordNameAttribute()
    {
        if (Str::length($this->name) >= 32) {
            $firstLetterOfLastName = substr($this->name_last, 0, 1);
            return "{$this->name_preferred} {$firstLetterOfLastName} - {$this->id}";
        }

        return "{$this->name} - {$this->id}";
    }

    /**
     * Sync the current account to Discord.
     */
    public function syncToDiscord()
    {
        if (! config('services.discord.token')) {
            return;
        }

        /** @var Discord */
        $discord = app()->make(Discord::class);

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
            if ($currentRoles->contains($suspendedRoleId)) {
                return;
            }

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
        if ($currentRoles->contains($suspendedRoleId)) {
            $discord->removeRoleById($this, $suspendedRoleId);
        }

        // Evaluate available discord roles
        $discordRoleRules = DiscordRoleRule::all()->map(function (DiscordRoleRule $roleRule) {
            return ['discord_id' => $roleRule->discord_id, 'satisfied' => $roleRule->accountSatisfies($this)];
        });

        // Group each of the role rules by the discord role id (there could be multiple rules for a single discord role). We then evaluate each grouped set, to see if the user has any of the rules satisified
        $discordRoleRules->groupBy('discord_id')->each(function ($groupedRoleRules, $discordRoleId) use ($currentRoles, $discord) {
            if (collect($groupedRoleRules)->contains(fn ($rule) => (bool) $rule['satisfied'])) {
                // At least one role rule grants this discord role. We will give it to the user if they don't already have it
                if (! $currentRoles->contains($discordRoleId)) {
                    Log::info("{$this->full_name} ({$this->getKey()}) should have discord role {$discordRoleId}, but doesn't");
                    $discord->grantRoleById($this, $discordRoleId);
                    sleep(1);
                }

                return;
            }

            if ($currentRoles->contains($discordRoleId)) {
                // None of the rules grant this role. We will remove it if they have it
                Log::info("{$this->full_name} ({$this->getKey()}) shouldn't have discord role {$discordRoleId}, but has it");
                $discord->removeRoleById($this, $discordRoleId);
                sleep(1);
            }
        });
    }
}
