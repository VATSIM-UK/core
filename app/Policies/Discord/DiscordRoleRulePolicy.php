<?php

namespace App\Policies\Discord;

use App\Models\Discord\DiscordRoleRule;
use App\Models\Mship\Account;

class DiscordRoleRulePolicy
{
    public function viewAny(Account $user): bool
    {
        return $user->can('discord.role-rule.view');
    }

    public function view(Account $user, DiscordRoleRule $rule): bool
    {
        return $user->can('discord.role-rule.view');
    }

    public function create(Account $user): bool
    {
        return $user->can('discord.role-rule.edit');
    }

    public function update(Account $user, DiscordRoleRule $rule): bool
    {
        return $user->can('discord.role-rule.edit');
    }

    public function delete(Account $user, DiscordRoleRule $rule): bool
    {
        return $user->can('discord.role-rule.edit');
    }
}
