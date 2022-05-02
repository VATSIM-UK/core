<?php

namespace App\Console\Commands\ExternalServices;

use App\Console\Commands\Command;
use App\Jobs\Mship\SyncToDiscord;
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

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $accounts = $this->getUsers() ?? collect();

        foreach ($accounts as $account) {
            SyncToDiscord::dispatch($account);
        }
    }

    private function getUsers()
    {
        if ($this->option('force')) {
            return Account::whereNotNull('discord_id')->where('id', $this->option('force'))->lazy();
        }

        return Account::whereNotNull('discord_id')->lazy();
    }
}
