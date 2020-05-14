<?php

namespace App\Models\Mship\Concerns;

use Illuminate\Support\Facades\DB;

/**
     * Trait HasCTSAccount
     */
    trait HasCTSAccount
    {
        /**
         * Sync the current account to the CTS.
         */
        public function syncToCTS()
        {
            $ctsDatabase = config('services.cts.database');
            $ssoAccountId = DB::table('oauth_clients')->where('name', 'CT System')->first();
            if (!$ssoAccountId || !$ctsDatabase) {
                return;
            }

            $ssoAccountId = $ssoAccountId->id;

            // Check user exists in database

            $ctsAccount = DB::table("{$ctsDatabase}.members")->where('cid', $this->id)->first();

            if (!$ctsAccount) {
                // No user exists. Abort.
                return;
            }

            $data = [
                'name' => $this->real_name,
                'email' => $this->getEmailForService($ssoAccountId),
                'rating' => ($this->network_banned || $this->inactive) ? 0 : $this->qualification_atc->vatsim,
                'prating' => $this->qualifications_pilot->sum('vatsim'),
                'last_cert_check' => $this->cert_checked_at,
            ];

            DB::table("{$ctsDatabase}.members")
                ->where('cid', $this->id)
                ->update($data);
        }
    }
