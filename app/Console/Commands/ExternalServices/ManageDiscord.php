<?php

namespace App\Console\Commands\ExternalServices;

use App\Console\Commands\Command;
use App\Libraries\Discord;
use App\Models\Discord\DiscordRole;
use App\Models\Mship\Account;

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

    /** @var int */
    private $suspendedRoleId;

    /** @var mixed */
    public $currentRoles;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Discord $discord)
    {
        parent::__construct();

        $this->discord = $discord;

        $this->currentRoles = collect();

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

        $progressBar = $this->output->createProgressBar(count($discordUsers));
        $progressBar->start();

        if (! $discordUsers) {
            $this->error('No users found.');
            exit();
        }

        foreach ($discordUsers as $account) {
            $this->currentRoles = $this->discord->getUserRoles($account);

            if ($account->isBanned) {
                $this->processSuspendedMember($account);
            } else {
                $this->grantRoles($account);
                $this->removeRoles($account);
            }

            $this->assignNickname($account);

            $progressBar->advance();
        }

        $progressBar->finish();
    }

    protected function getUsers()
    {
        if ($this->option('force')) {
            return Account::whereNotNull('discord_id')->where('id', $this->option('force'))->lazy();
        }

        return Account::whereNotNull('discord_id')->lazy();
    }

    protected function assignNickname(Account $account)
    {
        $this->discord->setNickname($account, $account->name);
        usleep(25000);
    }

    public function grantRoles(Account $account): void
    {
        $currentRoles = $this->currentRoles;

        DiscordRole::all()->filter(function (DiscordRole $role) use ($account) {
            return $account->hasPermissionTo($role->permission_id);
        })->each(function (DiscordRole $role) use ($account, $currentRoles) {
            if (! $currentRoles->contains($role->discord_id)) {
                $this->discord->grantRoleById($account, $role->discord_id);
                usleep(25000);
            }
        });
    }

    public function removeRoles(Account $account): void
    {
        $currentRoles = $this->currentRoles;

        if ($currentRoles->contains($this->suspendedRoleId)) {
            $this->discord->removeRoleById($account, $this->suspendedRoleId);
        }

        DiscordRole::all()->filter(function (DiscordRole $role) use ($account) {
            return ! $account->hasPermissionTo($role->permission_id);
        })->each(function (DiscordRole $role) use ($account, $currentRoles) {
            if ($currentRoles->contains($role->discord_id)) {
                $this->discord->removeRoleById($account, $role->discord_id);
                usleep(25000);
            }
        });
    }

    public function processSuspendedMember(Account $account): void
    {
        $currentRoles = $this->currentRoles;

        if ($currentRoles->contains($this->suspendedRoleId)) {
            return;
        }

        $currentRoles->each(function (int $role) use ($account) {
            $this->discord->removeRoleById($account, $role);
            usleep(25000);
        });

        $this->discord->grantRoleById($account, $this->suspendedRoleId);
    }
}
