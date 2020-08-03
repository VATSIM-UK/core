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

    /** @var Account */
    protected $account;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Discord $discord)
    {
        parent::__construct();

        $this->discord = $discord;
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
            $this->account = $account;
            $this->grantRoles();
            $this->removeRoles();
            $this->assignNickname();
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

    protected function assignNickname()
    {
        $this->discord->setNickname($this->account, $this->account->name);
    }

    protected function grantRoles()
    {
        $account = $this->account;
        $discord = $this->discord;

        DiscordRole::all()->filter(function ($value) use ($account) {
            return $account->hasPermissionTo($value['permission_id']);
        })->each(function ($value) use ($account, $discord) {
            $discord->grantRoleById($account, $value['discord_id']);
        });
    }

    protected function removeRoles()
    {
        $account = $this->account;
        $discord = $this->discord;

        DiscordRole::all()->filter(function ($value) use ($account) {
            return ! $account->hasPermissionTo($value['permission_id']);
        })->each(function ($value) use ($account, $discord) {
            $discord->removeRoleById($account, $value['discord_id']);
        });
    }
}
