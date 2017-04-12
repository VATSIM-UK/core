<?php

namespace App\Console\Commands;

use DB;
use App\Models\Mship\Account;

/**
 * Runs nightly to sync Core users to Moodle.
 *
 * This script was moved into Core without improvements, and is therefore rather database-intensive. Future work may
 * need doing after Moodle starts utilising Core SSO.
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
     * @return mixed
     */
    public function handle()
    {
        $this->sso_account_id = DB::table('sso_account')->where('username', 'vuk.moodle')->first()->id;

        DB::table('vatuk_moodle.mdl_user')->update(['vatuk_cron' => 0]);

        $members_moodle = DB::table('vatuk_moodle.mdl_user')
            ->get(['username', 'auth', 'deleted', 'firstname', 'lastname', 'email']);

        Account::whereNotNull('last_login')->with('states', 'qualifications', 'bans')->chunk(500, function ($members) use ($members_moodle) {
            foreach ($members as $member) {
                $this->log('Processing '.$member->id.': ', null, false);

                $ssoEmail = $member->ssoEmails->filter(function ($ssoemail) {
                    return $ssoemail->sso_account_id === $this->sso_account_id;
                })->first();
                $email = is_null($ssoEmail) ? $member->email : $ssoEmail->email->email;

                $inGoodStanding = ($member->hasState('DIVISION')
                        || $member->hasState('VISITING')
                        || $member->hasState('TRANSFERRING'))
                    && !$member->is_banned;
                $moodleUser = $members_moodle->search(function ($member_moodle) use ($member) {
                    return "{$member->id}" === $member_moodle->username;
                });

                if ($inGoodStanding && $moodleUser === false) {
                    $this->log('User does not exist, creating', 'comment');
                    DB::table('vatuk_moodle.mdl_user')->insert([
                        'auth' => 'vatsim',
                        'deleted' => 0,
                        'confirmed' => 1,
                        'policyagreed' => 1,
                        'mnethostid' => 1,
                        'username' => $member->id,
                        'password' => md5(str_random(60)),
                        'firstname' => $member->name_first,
                        'lastname' => $member->name_last,
                        'email' => $email,
                        'vatuk_cron' => 1,
                    ]);
                } elseif ($moodleUser) {
                    $old = [
                        'auth' => $members_moodle[$moodleUser]->auth,
                        'deleted' => $members_moodle[$moodleUser]->deleted,
                        'firstname' => $members_moodle[$moodleUser]->firstname,
                        'lastname' => $members_moodle[$moodleUser]->lastname,
                        'email' => $members_moodle[$moodleUser]->email,
                        'vatuk_cron' => 1,
                    ];

                    $new = [
                        'auth' => $inGoodStanding ? 'vatsim' : 'nologin',
                        'deleted' => $inGoodStanding ? 0 : 1,
                        'firstname' => $member->name_first,
                        'lastname' => $member->name_last,
                        'email' => $email,
                        'vatuk_cron' => 1,
                    ];

                    $dirty = array_keys(array_diff_assoc($old, $new));
                    if (!empty($dirty)) {
                        $output = 'User exists, updating:';
                        foreach ($dirty as $key) {
                            $output .= " || $key: $old[$key] -> $new[$key]";
                        }

                        $this->log($output, 'comment');
                        DB::table('vatuk_moodle.mdl_user')->where('username', $member->id)->update($new);
                    } else {
                        $this->log('User exists and is up to date', 'info');
                        DB::table('vatuk_moodle.mdl_user')->where('username', $member->id)->update(['vatuk_cron' => 1]);
                    }
                } else {
                    $this->log('User does not exist and is not eligible', 'info');
                }
            }
        });

        DB::table('vatuk_moodle.mdl_user')->where('vatuk_cron', 0)->update(['auth' => 'nologin', 'deleted' => 1]);
        DB::table('vatuk_moodle.mdl_user')->update(['vatuk_cron' => 0]);
    }
}
