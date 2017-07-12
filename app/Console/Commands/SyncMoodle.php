<?php

namespace App\Console\Commands;

use DB;
use App\Models\Mship\Account;

/**
 * Runs nightly to sync Core users to Moodle.
 */
class SyncMoodle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Sync:Moodle';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronises members with Moodle.';

    protected $sso_account_id;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $moodleAccounts = DB::table('vatuk_moodle.mdl_user')
            ->get(['username', 'auth', 'deleted', 'firstname', 'lastname', 'email', 'idnumber'])
            ->keyBy(function ($moodleAccount) {
                return $moodleAccount->username;
            });

        Account::whereNotNull('last_login')->with('states', 'qualifications', 'bans')->chunk(500, function ($coreAccounts) use (&$moodleAccounts) {
            /** @var Account $coreAccount */
            foreach ($coreAccounts as $coreAccount) {
                $moodleAccountKey = $moodleAccounts->search(function ($moodleAccount) use ($coreAccount) {
                    return "{$coreAccount->id}" === $moodleAccount->username;
                });

                $moodleAccount = $moodleAccountKey !== false ? $moodleAccounts[$moodleAccountKey] : false;

                $coreAccount->syncToMoodle($moodleAccount);

                unset($moodleAccounts[$coreAccount->id]);
            }
        });

        DB::table('vatuk_moodle.mdl_user')
            ->where(function ($query) {
                $query->where('auth', '!=', 'nologin')
                    ->orWhere('deleted', '!=', 1);
            })->whereIn('username', $moodleAccounts)
            ->update(['auth' => 'nologin', 'deleted' => 1]);
    }
}
