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
     * @return void
     */
    public function handle()
    {
        $this->sso_account_id = DB::table('oauth_clients')->where('name', 'Moodle')->first()->id;

        $members_moodle = DB::table('vatuk_moodle.mdl_user')
            ->get(['username', 'auth', 'deleted', 'firstname', 'lastname', 'email', 'idnumber'])
            ->keyBy(function ($member) {
                return $member->username;
            });

        Account::whereNotNull('last_login')->with('states', 'qualifications', 'bans')->chunk(500, function ($members) use ($members_moodle) {
            foreach ($members as $member) {
                $this->log('Processing '.$member->id.': ', null, false);

                $ssoEmail = $member->ssoEmails->filter(function ($ssoemail) {
                    return $ssoemail->sso_account_id === $this->sso_account_id;
                })->first();
                $email = is_null($ssoEmail) ? $member->email : $ssoEmail->email->email;

                $allowLogin = ($member->hasState('DIVISION')
                        || $member->hasState('VISITING')
                        || $member->hasState('TRANSFERRING'))
                    && !$member->is_banned;
                $moodleUser = $members_moodle->search(function ($member_moodle) use ($member) {
                    return "{$member->id}" === $member_moodle->username;
                });

                if ($allowLogin && $moodleUser === false) {
                    $this->createUser($member, $email);
                } elseif ($moodleUser !== false) {
                    $this->updateUser($member, $email, $allowLogin, $members_moodle[$moodleUser]);
                } else {
                    $this->log('User does not exist and is not eligible', 'info');
                }

                unset($members_moodle[$member->id]);
            }
        });

        DB::table('vatuk_moodle.mdl_user')->whereIn('username', $members_moodle)
            ->update(['auth' => 'nologin', 'deleted' => 1]);
    }

    protected function createUser($member, $email)
    {
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
    }

    protected function updateUser($member, $email, $allowLogin, $moodleAccount)
    {
        $old = [
            'auth' => $moodleAccount->auth,
            'deleted' => $moodleAccount->deleted,
            'firstname' => $moodleAccount->firstname,
            'lastname' => $moodleAccount->lastname,
            'email' => $moodleAccount->email,
            'idnumber' => $moodleAccount->idnumber,
            'vatuk_cron' => 1,
        ];

        $new = [
            'auth' => $allowLogin ? 'vatsim' : 'nologin',
            'deleted' => $allowLogin ? 0 : 1,
            'firstname' => $member->name_first,
            'lastname' => $member->name_last,
            'email' => $email,
            'idnumber' => $member->id,
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
        }
    }
}
