<?php

namespace App\Jobs\Mship;

use App\Models\Mship\Account;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class SyncToCTS implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $account;

    public function __construct(Account $account)
    {
        $this->account = $account;
    }

    public function handle()
    {
        $cts_database = config('services.cts.database');
        $sso_account_id = DB::table('oauth_clients')->where('name', 'CT System')->first();
        if (!$sso_account_id) {
            return;
        }

        $sso_account_id = $sso_account_id->id;

        // Check user exists in database

        $cts_account = DB::table("{$cts_database}.members")->where('cid', $this->account->id)->first();

        if (!$cts_account) {
            // No user exists. Abort.
            return;
        }

        $data = [
            'name' => $this->account->real_name,
            'email' => $this->account->getEmailForService($sso_account_id),
            'rating' => ($this->account->network_banned || $this->account->inactive) ? 0 : $this->account->qualification_atc->vatsim,
            'prating' => $this->account->qualifications_pilot->sum('vatsim'),
            'last_cert_check' => $this->account->cert_checked_at,
        ];

        DB::table("{$cts_database}.members")
                    ->where('cid', $this->account->id)
                    ->update($data);
    }
}
