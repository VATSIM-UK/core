<?php

namespace App\Models\Mship\Concerns;

use Carbon\Carbon;
use DB;

/**
 * Trait HasHelpdeskAccount
 */
trait HasHelpdeskAccount
{
    protected static $sso_account_id;

    /**
     * Sync the current account to Helpdesk.
     *
     * @param mixed $helpdeskAccount
     */
    public function syncToHelpdesk($helpdeskAccount = null)
    {
        if (!isset(self::$sso_account_id)) {
            self::$sso_account_id = DB::table('oauth_clients')->where('name', 'Helpdesk')->first()->id;
        }

        if ($helpdeskAccount) {
            $this->updateHelpdeskAccount($helpdeskAccount);
        } else {
            $this->createHelpdeskAccount();
        }
    }

    /**
     * Create a new account for the user in Moodle.
     *
     * @param string $email
     */
    protected function createHelpdeskAccount()
    {
        $emailInUse = DB::table(config('services.helpdesk.database').'.ost_user_email')
            ->whereIn('user_id', DB::table(config('services.helpdesk.database').'.ost_user_account')->select('user_id'))
            ->where('address', $this->getHelpdeskEmail())
            ->first();

        if (!$emailInUse) {
            $now = Carbon::now();
            $userId = DB::table(config('services.helpdesk.database').'.ost_user')
                ->insertGetId([
                    'org_id' => 0,
                    'status' => 0,
                    'name' => $this->name,
                    'created' => $now,
                    'updated' => $now,
                ]);

            $user = DB::table(config('services.helpdesk.database').'.ost_user')
                ->where('id', $userId)
                ->first(['ost_user.id', 'ost_user.name']);
            $user->account_id = $user->username = $user->cdata_cid = $user->email_id = $user->email = null;

            $this->updateHelpdeskAccount($user);
        }
    }

    /**
     * Update the user's existing Helpdesk account.
     *
     * @param mixed $helpdeskAccount
     */
    protected function updateHelpdeskAccount($helpdeskAccount)
    {
        $this->updateHelpdeskNameAndEmail($helpdeskAccount);
        $this->updateHelpdeskUsername($helpdeskAccount);

        if ($helpdeskAccount->cdata_cid !== (string) $this->id) {
            $existingCdata = DB::table(config('services.helpdesk.database').'.ost_user__cdata')
                ->where('user_id', $helpdeskAccount->id)
                ->first(['user_id']);

            if ($existingCdata) {
                DB::table(config('services.helpdesk.database').'.ost_user__cdata')
                    ->where('user_id', $existingCdata->user_id)
                    ->update(['cid' => $this->id]);
            } else {
                DB::table(config('services.helpdesk.database').'.ost_user__cdata')
                    ->insert(['user_id' => $helpdeskAccount->id, 'cid' => $this->id]);
            }
        }
    }

    protected function updateHelpdeskNameAndEmail($helpdeskAccount)
    {
        $emailId = null;
        $newEmail = $this->getHelpdeskEmail();
        if ($helpdeskAccount->email !== $newEmail) {
            $emailInUse = DB::table(config('services.helpdesk.database').'.ost_user_email')
                ->where('address', $newEmail)
                ->where('ost_user_email.user_id', '!=', $helpdeskAccount->id)
                ->whereIn('user_id', DB::table(config('services.helpdesk.database').'.ost_user_account')->select('user_id'))
                ->exists();

            if (!$emailInUse) {
                // delete old emails from the database
                DB::table(config('services.helpdesk.database').'.ost_user_email')
                    ->where('user_id', $helpdeskAccount->id)->orWhere('address', $newEmail)
                    ->delete();

                $emailId = DB::table(config('services.helpdesk.database').'.ost_user_email')
                    ->insertGetId([
                        'address' => $newEmail,
                        'flags' => 0,
                        'user_id' => $helpdeskAccount->id,
                    ]);
            }
        }

        $name = null;
        if ($helpdeskAccount->name !== $this->name) {
            $name = $this->name;
        }

        if ($emailId || $name) {
            DB::table(config('services.helpdesk.database').'.ost_user')
                ->where('id', $helpdeskAccount->id)
                ->update([
                    'name' => $name ?: $helpdeskAccount->name,
                    'default_email_id' => $emailId ?: $helpdeskAccount->email_id,
                ]);
        }
    }

    protected function updateHelpdeskUsername($helpdeskAccount)
    {
        if ($helpdeskAccount->username !== (string) $this->id) {
            if ($helpdeskAccount->account_id) {
                DB::table(config('services.helpdesk.database').'.ost_user_account')
                    ->where('id', $helpdeskAccount->account_id)
                    ->update(['username' => $this->id]);
            } else {
                DB::table(config('services.helpdesk.database').'.ost_user_account')
                    ->insert([
                        'user_id' => $helpdeskAccount->id,
                        'status' => 1,
                        'timezone' => 'UTC',
                        'username' => $this->id,
                        'extra' => '{"browser_lang":"en_US"}',
                        'registered' => Carbon::now(),
                    ]);
            }
        }
    }

    /**
     * Get the email to use for the Helpdesk account.
     *
     * @return string
     */
    protected function getHelpdeskEmail()
    {
        $ssoEmail = $this->ssoEmails->filter(function ($ssoemail) {
            return $ssoemail->sso_account_id === self::$sso_account_id;
        })->first();

        return is_null($ssoEmail) ? $this->email : $ssoEmail->email->email;
    }
}
