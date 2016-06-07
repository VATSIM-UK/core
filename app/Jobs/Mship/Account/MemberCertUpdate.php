<?php

namespace App\Jobs\Mship\Account;

use App\Exceptions\Mship\DuplicateQualificationException;
use App\Jobs\Job;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification as QualificationData;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use VatsimXML;

class MemberCertUpdate extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $accountID;
    protected $data;

    /**
     * Create a new job instance.
     */
    public function __construct($accountID)
    {
        $this->accountID = $accountID;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::beginTransaction();

        $this->data = VatsimXML::getData($this->accountID, 'idstatusint');
        $member = Account::find($this->accountID);

        if ($this->data->name_first == new \stdClass()
            && $this->data->name_last == new \stdClass()
            && $this->data->email == '[hidden]'
        ) {
            $member->delete();
            return;
        }

        if (!empty($this->data->name_first) && is_string($this->data->name_first)) {
            $member->name_first = $this->data->name_first;
        }

        if (!empty($this->data->name_last) && is_string($this->data->name_last)) {
            $member->name_last = $this->data->name_last;
        }

        $member->cert_checked_at = Carbon::now();
        $member->is_inactive = (boolean) ($this->data->rating < 0);
        $member->joined_at = $this->data->regdate;
        $member->determineState($this->data->region, $this->data->division);

        $this->processBans($member);
        try {
            $member = $this->processRating($member);
        } catch(DuplicateQualificationException $e){
            // TODO: Something.
        }

        $member->save();
        DB::commit();
    }

    protected function processBans($member)
    {
        // if their network ban needs adding
        if ($this->data->rating == 0 && $member->is_network_banned === false){
            // Add a ban.
            $newBan = new Account\Ban();
            $newBan->type = Account\Ban::TYPE_NETWORK;
            $newBan->reason_extra = 'Network ban discovered via Cert update scripts.';
            $newBan->period_start = Carbon::now();
            $newBan->save();

            $member->bans()->save($newBan);
            Account::find(VATSIM_ACCOUNT_SYSTEM)->bansAsInstigator($newBan);
        }

        // if their network ban has expired
        if ($member->is_network_banned === true && $this->data->rating != 0) {
            $ban = $member->network_ban;
            $ban->period_finish = Carbon::now();
            $ban->save();
        }
    }

    protected function processRating($member)
    {
        // if they have an extra rating, log their previous rating
        if ($this->data->rating >= 8) {
            $_prevRat = VatsimXML::getData($member->id, 'idstatusprat');
            if (isset($_prevRat->PreviousRatingInt)) {
                $prevAtcRating = QualificationData::parseVatsimATCQualification($_prevRat->PreviousRatingInt);
                if (!$member->hasQualification($prevAtcRating)) {
                    $member->addQualification($prevAtcRating);
                }
            }
        } else {
            // remove any extra ratings
            foreach ($member->qualifications_atc_training as $qual) {
                $qual->delete();
            }
            foreach ($member->qualifications_pilot_training as $qual) {
                $qual->delete();
            }
            foreach ($member->qualifications_admin as $qual) {
                $qual->delete();
            }
        }

        // log their current rating (unless they're a non-UK instructor)
        if (($this->data->rating != 8 && $this->data->rating != 9)
            || $member->current_state->state == Account\State::STATE_DIVISION
        ) {
            $atcRating = QualificationData::parseVatsimATCQualification($this->data->rating);
            if (!$member->hasQualification($atcRating)) {
                $member->addQualification($atcRating);
            }
        }

        $pilotRatings = QualificationData::parseVatsimPilotQualifications($this->data->pilotrating);
        foreach ($pilotRatings as $pr) {
            if (!$member->hasQualification($pr)) {
                $member->addQualification($pr);
            }
        }

        return $member;
    }
}
