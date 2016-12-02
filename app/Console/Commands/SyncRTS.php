<?php

namespace App\Console\Commands;

use DB;
use App\Models\Mship\Account;

class SyncRTS extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'Sync:RTS
                        {--f|force=0 : If specified, only this CID will be checked.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync membership data from Core to the RTS system.';

    protected $sso_account_id;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->sso_account_id = DB::table('sso_account')->where('username', 'vuk.rts')->first()->id;

        $this->log("RTS DIVISION DATABASE IMPORT STARTED\n");

        $members = DB::table('prod_rts.members');
        if ($this->option('force')) {
            $members->where('cid', '=', $this->option('force'))
                    ->where('deleted', '=', '0');
        } else {
            $members->where('deleted', '=', '0')
                    ->orderBy('cid');
        }

        $members = $members->get();

        $output = 'Querying members...';
        $numupdated = 0;

        $this->log($output."OK.\n");

        foreach ($members as $mem) {
            $output = "Updating {$mem->cid} ";
            if ($this->pullCoreData($mem->cid, $mem->visiting)) {
                $this->log($output.'...... Successful');
            } else {
                $this->log('...... FAILED');
            }
            $numupdated++;
        }

        $this->log("\n\n$numupdated members were updated\nRTS SYNC COMPLETED\n");
    }

    protected function pullCoreData($cid, $ignoreRating = false)
    {
        // get account
        try {
            $member = Account::findOrFail($cid);
        } catch (\Exception $e) {
            $this->log("\tError: cannot retrieve member ".$cid.' from Core - '.$e->getMessage());

            return false;
        }

        // calculate pilot rating
        $pRating = 0;
        $pQuals = $member->qualifications_pilot;
        if (count($pQuals) > 0) {
            foreach ($pQuals as $qual) {
                $pRating += $qual->vatsim;
            }
        }

        // set and process data
        $email = $member->email;
        $sso_account_id = $this->sso_account_id;
        $ssoEmailAssigned = $member->ssoEmails->filter(function ($ssoemail) use ($sso_account_id) {
            return $ssoemail->sso_account_id == $sso_account_id;
        })->values();

        if ($ssoEmailAssigned && count($ssoEmailAssigned) > 0) {
            $email = $ssoEmailAssigned[0]->email->email;
        }

        $updateData = [
            'name' => $member->name_first.' '.$member->name_last,
            'email' => $email,
            'rating' => $member->qualification_atc->vatsim,
            'prating' => $pRating,
            'last_cert_check' => $member->cert_checked_at,
        ];

        if ($member->network_banned || $member->inactive) {
            $updateData['rating'] = 0;
        }
        if ($ignoreRating) {
            unset($updateData['rating']);
            unset($updateData['prating']);
        }
        if (empty($updateData['email'])) {
            unset($updateData['email']);
        }

        DB::table('prod_rts.members')
            ->where('cid', '=', $cid)
            ->update($updateData);

        return true;
    }
}
