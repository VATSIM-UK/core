<?php

namespace App\Console\Commands\ExternalServices;

use App\Console\Commands\Command;
use App\Libraries\Discord;
use App\Models\Discord\DiscordRole;
use App\Models\Mship\Account;
use Illuminate\Support\Facades\Log;

class ManageDiscord extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discord:manager
                            {--f|force= : If specified, only this CID will be updated.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure Discord users are in sync with VATSIM UK\'s data';

    /** @var Discord */
    protected $discord;

    private $suspendedRoleId;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Discord $discord)
    {
        parent::__construct();

        $this->discord = $discord;

        $this->suspendedRoleId = config('services.discord.suspended_member_role_id');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $discordUsers = $this->getUsers();

        if (! $discordUsers) {
            $this->error('No users found.');
            exit();
        }

        foreach ($discordUsers as $account) {
            if ($account->isBanned) {
                $this->processSuspendedMember($account);
            } else {
                $this->grantRoles($account);
                $this->removeRoles($account);
            }
            $this->assignNickname($account);
            sleep(1);
        }

        $this->info($discordUsers->count().' user(s) updated on Discord.');
        Log::debug($discordUsers->count().' user(s) updated on Discord.');
    }

    protected function getUsers()
    {
        if ($this->option('force')) {
            return Account::where('id', $this->option('force'))->where('discord_id', '!=', null)->get();
        }

        return Account::where('discord_id', '!=', null)->get();
    }

    protected function assignNickname(Account $account)
    {
        $this->discord->setNickname($account, $account->name);
    }

    public function grantRoles(Account $account): void
    {
        $currentRoles = $this->discord->getUserRoles($account);

        DiscordRole::all()->filter(function (DiscordRole $role) use ($account) {
            return $account->hasPermissionTo($role->permission_id);
        })->each(function (DiscordRole $role) use ($account, $currentRoles) {
            if (! $currentRoles->contains($role->discord_id)) {
                $this->discord->grantRoleById($account, $role->discord_id);
                sleep(1);
            }
        });
    }

    public function removeRoles(Account $account): void
    {
        $currentRoles = $this->discord->getUserRoles($account);

        if ($currentRoles->contains($this->suspendedRoleId)) {
            $this->discord->removeRoleById($account, $this->suspendedRoleId);
        }

        DiscordRole::all()->filter(function (DiscordRole $role) use ($account) {
            return ! $account->hasPermissionTo($role->permission_id);
        })->each(function (DiscordRole $role) use ($account, $currentRoles) {
            if ($currentRoles->contains($role->discord_id)) {
                $this->discord->removeRoleById($account, $role->discord_id);
                sleep(1);
            }
        });
    }

    /**
     * Process the relevant actions for a suspended user.
     *
     * All roles are removed and a suspended role added onto the
     * Account.
     *
     * @param Account $account
     * @return void
     */
    public function processSuspendedMember(Account $account): void
    {
        Log::info("Account {$account->id} detected as suspended. Removing Discord roles.");
        $currentRoles = $this->discord->getUserRoles($account);

        // remove the roles which are currently applied to the user.
        $currentRoles->each(function (int $role) use ($account) {
            $this->discord->removeRoleById($account, $role);
            sleep(1); // avoid spamming the Discord API.
        });

        $this->discord->grantRoleById($account, $this->suspendedRoleId);
        Log::info("Account {$account->id} granted the suspended role.");
    }
}
