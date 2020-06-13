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
    protected $signature = 'discord:manager';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure Discord users are in sync with VATSIM UK\'s data';

    protected Discord $discord;

    protected Account $account;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->discord = app()->make(Discord::class);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $discordUsers = Account::where('discord_id', '!=', null)->get();

        foreach ($discordUsers as $account) {
            $this->account = $account;
            $this->grantRoles();
            $this->removeRoles();
            $this->assignNickname();
        }
    }

    protected function assignNickname()
    {
        $this->discord->setNickname($this->account, $this->account->name);
    }

    protected function grantRoles()
    {
        $account = $this->account;
        $discord = $this->discord;

        DiscordRole::all()->filter(function ($value) use ($account) {
            return $account->hasPermissionTo((int) $value['permission_id']);
        })->each(function ($value) use ($account, $discord) {
            $discord->grantRoleById($account, (int) $value['discord_id']);
        });
    }

    protected function removeRoles()
    {
        $account = $this->account;
        $discord = $this->discord;

        DiscordRole::all()->filter(function ($value) use ($account) {
            return ! $account->hasPermissionTo((int) $value['permission_id']);
        })->each(function ($value) use ($account, $discord) {
            $discord->removeRoleById($account, (int) $value['discord_id']);
        });
    }
}
