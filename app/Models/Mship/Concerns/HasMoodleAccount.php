<?php

namespace App\Models\Mship\Concerns;

use DB;

/**
 * Trait SyncsToMoodle
 */
trait HasMoodleAccount
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

        if ($moodleAccount === false && $this->canLoginToMoodle()) {
            $this->createMoodleAccount($this->getMoodleEmail());
        } elseif ($moodleAccount !== false) {
            $this->updateMoodleAccount($this->getMoodleEmail(), $this->canLoginToMoodle(), $moodleAccount);
        } else {
            // do nothing - user is not eligible for a Moodle account, nor do they have one already
        }
    }

    /**
     * Create a new account for the user in Moodle.
     *
     * @param string $email
     */
    protected function createMoodleAccount($email)
    {
        DB::table(config('services.moodle.database').'.mdl_user')->insert([
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
    protected function updateMoodleAccount($email, $allowLogin, $moodleAccount)
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
            'idnumber' => (string) $this->id,
            'vatuk_cron' => 1,
        ];

        $dirty = array_keys(array_diff_assoc($old, $new));
        if (!empty($dirty)) {
            DB::table(config('services.moodle.database').'.mdl_user')->where('username', (string) $this->id)->update($new);
        } else {
            // do nothing - account is up to date
        }
    }

    /**
     * Get the email to use for the Moodle account.
     *
     * @return string
     */
    protected function getMoodleEmail()
    {
        $ssoEmail = $this->ssoEmails->filter(function ($ssoemail) {
            return $ssoemail->sso_account_id === self::$sso_account_id;
        })->first();

        return is_null($ssoEmail) ? $this->email : $ssoEmail->email->email;
    }

    /**
     * Check whether the user should be able to login to Moodle.
     *
     * @return bool
     */
    protected function canLoginToMoodle()
    {
        return $this->hasState('DIVISION')
                || $this->hasState('VISITING')
                || $this->hasState('TRANSFERRING');
    }
}
