<?php

namespace App\Models\Mship\Concerns;

use DB;

/**
 * Trait SyncsToMoodle
 */
trait SyncsToMoodle
{
    protected static $sso_account_id;

    /**
     * Sync the current account to Moodle.
     *
     * @param mixed $moodleAccount Related Moodle account, false if doesn't exist
     */
    public function syncToMoodle($moodleAccount)
    {
        if (!isset(self::$sso_account_id)) {
            self::$sso_account_id = DB::table('oauth_clients')->where('name', 'Moodle')->first()->id;
        }

        $ssoEmail = $this->ssoEmails->filter(function ($ssoemail) {
            return $ssoemail->sso_account_id === self::$sso_account_id;
        })->first();
        $email = is_null($ssoEmail) ? $this->email : $ssoEmail->email->email;

        $allowLogin = ($this->hasState('DIVISION')
                || $this->hasState('VISITING')
                || $this->hasState('TRANSFERRING'))
            && !$this->is_banned;

        if ($allowLogin && $moodleAccount === false) {
            $this->createUser($email);
        } elseif ($moodleAccount !== false) {
            $this->updateUser($email, $allowLogin, $moodleAccount);
        } else {
            // do nothing - user is not eligible for a Moodle account, nor do they have one already
        }
    }

    /**
     * Create a new account for the user in Moodle.
     *
     * @param string $email
     */
    protected function createUser($email)
    {
        DB::table('vatuk_moodle.mdl_user')->insert([
            'auth' => 'vatsim',
            'deleted' => 0,
            'confirmed' => 1,
            'policyagreed' => 1,
            'mnethostid' => 1,
            'username' => $this->id,
            'password' => md5(str_random(60)),
            'firstname' => $this->name_first,
            'lastname' => $this->name_last,
            'email' => $email,
            'vatuk_cron' => 1,
        ]);
    }

    /**
     * Update the user's existing Moodle account.
     *
     * @param string $email
     * @param bool $allowLogin
     * @param mixed $moodleAccount
     */
    protected function updateUser($email, $allowLogin, $moodleAccount)
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
            'firstname' => $this->name_first,
            'lastname' => $this->name_last,
            'email' => $email,
            'idnumber' => $this->id,
            'vatuk_cron' => 1,
        ];

        $dirty = array_keys(array_diff_assoc($old, $new));
        if (!empty($dirty)) {
            DB::table('vatuk_moodle.mdl_user')->where('username', $this->id)->update($new);
        } else {
            // do nothing - account is up to date
        }
    }
}
