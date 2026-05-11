<?php

namespace App\Models\Mship\Concerns;

use App\Events\Discord\DiscordUnlinked;
use App\Exceptions\Discord\DiscordUserNotFoundException;
use App\Libraries\Discord;
use App\Models\Discord\DiscordRoleRule;
use Illuminate\Support\Collection;
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
                    $truncatedFirstName .= ($truncatedFirstName ? ' ' : '').$part;
                }

                if ($truncatedFirstName === '') {
                    $availableFirstNameLength = 32 - Str::length(" {$firstLetterOfLastName} - {$this->id}");
                    $truncatedFirstName = Str::substr($firstNameParts[0] ?? $this->name_preferred, 0, max($availableFirstNameLength, 1));
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
        }

        // Retrieve users current roles
        $currentRoles = $discord->getUserRoles($this);

        if ($currentRoles->isEmpty()) {
            return;
        }

        // Handle if the user is banned on core
        if ($this->isBanned) {
            // If they are already in the suspended role, we are happy
            if ($currentRoles->contains($suspendedRoleId)) {
                return;
            }

            // Set their roles to only the suspended role (replaces all current roles
            $discord->setRoles($this, [(int) $suspendedRoleId]);

            return;
        }

        // Compute desired roles based on DiscordRoleRules
        $targetRoles = $this->computeTargetRoles($currentRoles, $suspendedRoleId);

        // Only call the API if roles actually changed
        if ($this->rolesNeedUpdate($currentRoles, $targetRoles)) {
            Log::info("Updating Discord roles for {$this->full_name} ({$this->getKey()})", [
                'current' => $currentRoles->toArray(),
                'target' => $targetRoles->toArray(),
            ]);

            $discord->setRoles($this, $targetRoles->values()->toArray());
        }
    }

    /**
     * Compute the target set of Discord roles for this account based on DiscordRoleRule rules.
     *
     * - Managed roles (those with at least one DiscordRoleRule) are set to the
     *   satisfied set: present if any rule grants them, absent otherwise.
     * - Unmanaged roles are preserved as-is.
     * - The suspended role is always excluded from the result.
     */
    private function computeTargetRoles(Collection $currentRoles, $suspendedRoleId): Collection
    {
        $targetRoles = $currentRoles->reject(fn ($role) => (int) $role === (int) $suspendedRoleId);

        $discordRoleRules = DiscordRoleRule::all()->map(function (DiscordRoleRule $roleRule) {
            return ['discord_id' => $roleRule->discord_id, 'satisfied' => $roleRule->accountSatisfies($this)];
        });

        $managedRoleIds = $discordRoleRules->pluck('discord_id')->unique()->values();

        $satisfiedRoleIds = $discordRoleRules
            ->groupBy('discord_id')
            ->filter(fn (Collection $group) => $group->contains(fn ($rule) => $rule['satisfied']))
            ->keys();

        // Remove managed roles that aren't satisfied
        $targetRoles = $targetRoles->reject(fn ($role) => $managedRoleIds->contains((int) $role));

        return $targetRoles->merge($satisfiedRoleIds)->unique()->values();
    }

    /**
     * Check whether the current and target role sets differ.
     */
    private function rolesNeedUpdate(Collection $currentRoles, Collection $targetRoles): bool
    {
        return $currentRoles->sort()->values()->toArray() !== $targetRoles->sort()->values()->toArray();
    }
}
