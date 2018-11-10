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
        $ctsDatabse = config('services.cts.database');
        $ssoAccountId = DB::table('oauth_clients')->where('name', 'CT System')->first();
        if (!$ssoAccountId) {
            return;
        }

        $ssoAccountId = $ssoAccountId->id;

        // Check user exists in database

        $ctsAccount = DB::table("{$ctsDatabse}.members")->where('cid', $this->account->id)->first();

        if (!$ctsAccount) {
            // No user exists. Abort.
            return;
        }

        $data = [
            'name' => $this->account->real_name,
            'email' => $this->account->getEmailForService($ssoAccountId),
            'rating' => ($this->account->network_banned || $this->account->inactive) ? 0 : $this->account->qualification_atc->vatsim,
            'prating' => $this->account->qualifications_pilot->sum('vatsim'),
            'last_cert_check' => $this->account->cert_checked_at,
        ];

        DB::table("{$ctsDatabse}.members")
                    ->where('cid', $this->account->id)
                    ->update($data);
    }
}
