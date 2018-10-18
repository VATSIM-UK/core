<?php

namespace App\Jobs\Mship;

use App\Models\Mship\Account;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $sso_account_id = DB::table('oauth_clients')->where('name', 'CT System')->first()->id;

        // Check user exists in database

        $cts_user = DB::table("{$cts_database}.members")->where('cid', $this->account->id)->first();

        if(!$cts_user){
            // No user exists. Abort.
            return;
        }


        // Find correct email to use
        $user_cts_email = $this->account->ssoEmails()->where('sso_account_id', $sso_account_id)->with('email')->first();
        $email = $this->account->email;
        if($user_cts_email){
            $email = $user_cts_email->email->email;
        }

        $data = [
            'name' => $this->account->real_name,
            'email' => $email,
            'rating' => ($this->account->network_banned || $this->account->inactive) ? 0 : $this->account->qualification_atc->vatsim,
            'prating' => $this->account->qualifications_pilot->sum('vatsim'),
            'last_cert_check' => $this->account->cert_checked_at,
        ];


        DB::table("{$cts_database}.members")
                    ->where('cid', $this->account->id)
                    ->update($data);

        Log::debug($this->account->real_name . " synced to CTS");
    }
}
