<?php

namespace App\Models\Mship\Concerns;

use App\Events\Discord\DiscordUnlinked;
use App\Exceptions\Discord\DiscordUserNotFoundException;
use App\Exceptions\Discord\GenericDiscordException;
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
        // discord only permits a nickname of 32 characters.
        // in the event that the name + CID exceeds, truncate accordin
        $nameWithCid = "{$this->name} - {$this->id}";
        if (Str::length($nameWithCid) > 32) {
            // Attempt to truncate last name
            $firstLetterOfLastName = substr($this->name_last, 0, 1);

            if (Str::length("{$this->name_preferred} {$firstLetterOfLastName} - {$this->id}") > 32) {
                // Attempt as many parts of the first name as possible
                $firstNameParts = explode(' ', $this->name_preferred);
                $truncatedFirstName = '';
                foreach ($firstNameParts as $part) {
                    if (Str::length("{$truncatedFirstName} {$part} {$firstLetterOfLastName} - {$this->id}") > 32) {
                        break;
                    }
                    $truncatedFirstName .= ($truncatedFirstName ? ' ' : '') . $part;
                }

                return "{$truncatedFirstName} {$firstLetterOfLastName} - {$this->id}";
            }

            return "{$this->name_preferred} {$firstLetterOfLastName} - {$this->id}";
        }

        return $nameWithCid;
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
        } catch (GenericDiscordException $e) {
            Log::error('Discord sync: failed to set nickname, aborting sync', [
                'account_id' => $this->id,
                'discord_id' => $this->discord_id,
                'exception' => $e->getMessage(),
            ]);
            throw $e;
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
                try {
                    $discord->removeRoleById($this, $role);
                } catch (GenericDiscordException $e) {
                    Log::error('Discord sync: failed to remove role during ban enforcement', [
                        'account_id' => $this->id,
                        'discord_id' => $this->discord_id,
                        'role_id' => $role,
                        'exception' => $e->getMessage(),
                    ]);
                    throw $e;
                }
                sleep(1);
            });

            // Give them the suspended user role
            try {
                $discord->grantRoleById($this, $suspendedRoleId);
            } catch (GenericDiscordException $e) {
                Log::error('Discord sync: failed to grant suspended role', [
                    'account_id' => $this->id,
                    'discord_id' => $this->discord_id,
                    'role_id' => $suspendedRoleId,
                    'exception' => $e->getMessage(),
                ]);
                throw $e;
            }

            // We'll return, as suspended users should only have this suspended role
            return;
        }

        // If they have the suspended role, remove it (no longer suspended)
        if ($currentRoles->contains($suspendedRoleId)) {
            try {
                $discord->removeRoleById($this, $suspendedRoleId);
            } catch (GenericDiscordException $e) {
                Log::error('Discord sync: failed to remove suspended role', [
                    'account_id' => $this->id,
                    'discord_id' => $this->discord_id,
                    'role_id' => $suspendedRoleId,
                    'exception' => $e->getMessage(),
                ]);
                throw $e;
            }
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
                    try {
                        $discord->grantRoleById($this, $discordRoleId);
                    } catch (GenericDiscordException $e) {
                        Log::error('Discord sync: failed to grant role', [
                            'account_id' => $this->id,
                            'discord_id' => $this->discord_id,
                            'role_id' => $discordRoleId,
                            'exception' => $e->getMessage(),
                        ]);
                    }
                    sleep(1);
                }

                return;
            }

            if ($currentRoles->contains($discordRoleId)) {
                // None of the rules grant this role. We will remove it if they have it
                Log::info("{$this->full_name} ({$this->getKey()}) shouldn't have discord role {$discordRoleId}, but has it");
                try {
                    $discord->removeRoleById($this, $discordRoleId);
                } catch (GenericDiscordException $e) {
                    Log::error('Discord sync: failed to remove role', [
                        'account_id' => $this->id,
                        'discord_id' => $this->discord_id,
                        'role_id' => $discordRoleId,
                        'exception' => $e->getMessage(),
                    ]);
                }
                sleep(1);
            }
        });
    }
}
