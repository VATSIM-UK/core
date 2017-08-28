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
        $hdb = config('services.helpdesk.database');

        $helpdeskAccounts = DB::table($hdb.'.ost_user')
            ->leftJoin($hdb.'.ost_user_account', 'ost_user.id', '=', 'ost_user_account.user_id')
            ->leftJoin($hdb.'.ost_user_email', 'ost_user.id', '=', 'ost_user_email.user_id')
            ->get(['ost_user.id', 'ost_user_account.id as account_id', 'username', 'ost_user.name', 'ost_user_email.id as email_id', 'address as email'])
            ->keyBy('username');

        Account::whereNotNull('last_login')->whereNotNull('email')
            ->with('ssoEmails')
            ->chunk(500, function ($coreAccounts) use (&$helpdeskAccounts) {
                /** @var Account $coreAccount */
                foreach ($coreAccounts as $coreAccount) {
                    $helpdeskAccount = $helpdeskAccounts->get($coreAccount->id) ?: null;
                    $coreAccount->syncToHelpdesk($helpdeskAccount);
                }
            });
    }
}
