<?php

namespace App\Console\Commands\ExternalServices;

use App\Console\Commands\Command;
use App\Models\Mship\Account;
use DB;

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

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $moodleDB = config('services.moodle.database');

        $moodleAccounts = DB::table("{$moodleDB}.mdl_user")
            ->get(['username', 'auth', 'deleted', 'firstname', 'lastname', 'email', 'idnumber'])
            ->keyBy(function ($moodleAccount) {
                return $moodleAccount->username;
            });

        Account::whereNotNull('last_login')
            ->with('states', 'bans', 'ssoEmails')
            ->chunk(500, function ($coreAccounts) use (&$moodleAccounts) {
                /** @var Account $coreAccount */
                foreach ($coreAccounts as $coreAccount) {
                    $moodleAccount = $moodleAccounts->get($coreAccount->id) ?: false;
                    $coreAccount->syncToMoodle($moodleAccount);
                    unset($moodleAccounts[$coreAccount->id]);
                }
            });

        // soft-delete any Moodle users that weren't in Core
        DB::table("{$moodleDB}.mdl_user")
            ->where(function ($query) {
                $query->where('auth', '!=', 'nologin')
                    ->orWhere('deleted', '!=', 1);
            })->whereIn('username', $moodleAccounts->pluck('username'))
            ->update(['auth' => 'nologin', 'deleted' => 1]);
    }
}
