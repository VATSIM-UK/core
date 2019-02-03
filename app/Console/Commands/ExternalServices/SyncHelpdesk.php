<?php

namespace App\Console\Commands\ExternalServices;

use App\Console\Commands\Command;
use App\Models\Mship\Account;
use DB;

/**
 * Syncs all Core accounts to Helpdesk.
 */
class SyncHelpdesk extends Command
{
    protected $mainDatabase;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Sync:Helpdesk';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronises members with Helpdesk.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        Account::whereNotNull('last_login')->whereNotNull('email')
            ->with('ssoEmails')
            ->chunk(500, function ($coreAccounts) {
                /** @var Account $coreAccount */
                foreach ($coreAccounts as $coreAccount) {
                    $coreAccount->syncToHelpdesk();
                }
            });
    }
}
