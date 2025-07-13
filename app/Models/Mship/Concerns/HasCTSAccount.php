<?php

namespace App\Models\Mship\Concerns;

use App\Models\Cts\Member;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\DB;

/**
 * Trait HasCTSAccount.
 */
trait HasCTSAccount
{
    /**
     * Generate an internal identifier for the CTS user record.
     * See CTS codebase (cts/scripts/member.php) for implementation details on Ids which don't equate
     * to the CID of a user.
     */
    public function generateCTSInternalID(int $cid): int // NOSONAR
    {

        if ($cid < 800000) {
            return $cid + 2000000;
        } elseif ($cid < 1323000) {
            return $cid;
        } elseif ($cid < 1800000) {
            return $cid - 1000000;
        } else {
            return $cid + 2000000;
        }
    }

    /**
     * Return the member model relating to the account.
     * We cannot use a relationship here as the CTS member model is not in the same database connection.
     */
    public function member(): Attribute
    {
        return Attribute::make(
            fn () => Member::where('cid', $this->id)->first(),
        );
    }

    /**
     * Sync the current account to the CTS.
     */
    public function syncToCTS()
    {
        $ctsDatabase = config('services.cts.database');
        $ssoAccountId = DB::table('oauth_clients')->where('name', 'CT System')->first();
        if (! $ssoAccountId || ! $ctsDatabase) {
            return;
        }

        $ssoAccountId = $ssoAccountId->id;

        // Check user exists in database

        $ctsAccount = DB::table("{$ctsDatabase}.members")->where('cid', $this->id)->first();

        $shouldBeSynced = $this->states->some(function ($state) {
            return collect(['DIVISION', 'VISITING', 'TRANSFERRING'])->contains($state->code);
        });

        // if no CTS account exists, lets sync to create a new one, and not update anything this time round.
        if (! $ctsAccount && $shouldBeSynced) {
            // for a division member, use the join timestamp of them joining, else use an empty timestamp.
            // this is how CTS puts this column on visiting controllers.
            $is_visitor = $this->primary_permanent_state->code != 'DIVISION';
            $joined_div = ! $is_visitor
                ? $this->primary_permanent_state->pivot->start_at
                : null;

            $newMember = [
                'old_rts_id' => 0,
                'id' => $this->generateCTSInternalID($this->id),
                'cid' => $this->id,
                'name' => $this->full_name, // full_name respects any nicknames which have been set.
                'email' => $this->getEmailForService($ssoAccountId),
                'rating' => ($this->network_banned || $this->inactive) ? 0 : $this->qualification_atc->vatsim,
                'prating' => $this->qualifications_pilot->sum('vatsim'),
                'joined' => $this->joined_at,
                'joined_div' => $joined_div,
                'visiting' => $is_visitor,
                'last_cert_check' => $this->cert_checked_at,
            ];
            if ($is_visitor) {
                // do a best guess on the division they are visiting from. Designed to be updated later by an admin.
                $newMember['visit_from'] = "{$this->primary_permanent_state->pivot->region} - {$this->primary_permanent_state->pivot->division}";
            }
            DB::table("{$ctsDatabase}.members")->insert($newMember);

            // go no further.
            return;
        }

        $data = [
            'name' => $this->full_name,
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
