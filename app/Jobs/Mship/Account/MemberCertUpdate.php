<?php

namespace App\Jobs\Mship\Account;

use DB;
use VatsimXML;
use App\Jobs\Job;
use Carbon\Carbon;
use App\Models\Mship\Account;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Exceptions\Mship\DuplicateStateException;
use App\Models\Mship\Qualification as QualificationData;
use App\Exceptions\Mship\DuplicateQualificationException;

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
        if (!is_string($this->data->region)) {
            $this->data->region = '';
        }
        if (!is_string($this->data->division)) {
            $this->data->division = '';
        }

        $member = Account::find($this->accountID);

        // if member no longer exists, delete
        // else process update
        if ($this->data->name_first == new \stdClass()
            && $this->data->name_last == new \stdClass()
            && $this->data->email == '[hidden]'
        ) {
            $member->delete();
        } else {
            if (!empty($this->data->name_first) && is_string($this->data->name_first)) {
                $member->name_first = $this->data->name_first;
            }

            if (!empty($this->data->name_last) && is_string($this->data->name_last)) {
                $member->name_last = $this->data->name_last;
            }

            $member->cert_checked_at = Carbon::now();
            $member->is_inactive = (bool) ($this->data->rating < 0);
            $member->joined_at = $this->data->regdate;
            $member->save();

            try {
                $state = determine_mship_state_from_vatsim($this->data->region, $this->data->division);
                $member->addState($state, $this->data->region, $this->data->division);
            } catch (DuplicateStateException $e) {
                // Todo: Something.
            }

            $member = $this->processBans($member);
            $member = $this->processRating($member);

            $member->save();
        }

        DB::commit();
    }

    protected function processBans($member)
    {
        // if their network ban needs adding
        if ($this->data->rating == 0 && $member->is_network_banned === false) {
            // Add a ban.
            $newBan = new Account\Ban();
            $newBan->type = Account\Ban::TYPE_NETWORK;
            $newBan->reason_extra = 'Network ban discovered via Cert update scripts.';
            $newBan->period_start = Carbon::now();
            $newBan->save();

            $member->bans()->save($newBan);
        }

        // if their network ban has expired
        if ($member->is_network_banned === true && $this->data->rating != 0) {
            $ban = $member->network_ban;
            $ban->period_finish = Carbon::now();
            $ban->save();
        }

        return $member;
    }

    protected function processRating($member)
    {
        try {
            // if they have an extra rating, log their previous rating
            if ($this->data->rating >= 8) {
                $_prevRat = VatsimXML::getData($member->id, 'idstatusprat');
                if (isset($_prevRat->PreviousRatingInt)) {
                    $prevAtcRating = QualificationData::parseVatsimATCQualification($_prevRat->PreviousRatingInt);
                    if (!is_null($prevAtcRating) && !$member->hasQualification($prevAtcRating)) {
                        $member->addQualification($prevAtcRating);
                    }
                }
            } else {
                // remove any extra ratings
                foreach ($member->qualifications_atc_training as $qual) {
                    $qual->pivot->delete();
                }
                foreach ($member->qualifications_pilot_training as $qual) {
                    $qual->pivot->delete();
                }
                foreach ($member->qualifications_admin as $qual) {
                    $qual->pivot->delete();
                }
            }

            // log their current rating (unless they're a non-UK instructor)
            if (($this->data->rating != 8 && $this->data->rating != 9) || $member->hasState('DIVISION')
            ) {
                $atcRating = QualificationData::parseVatsimATCQualification($this->data->rating);
                if (!is_null($atcRating) && !$member->hasQualification($atcRating)) {
                    $member->addQualification($atcRating);
                }
            }

            $pilotRatings = QualificationData::parseVatsimPilotQualifications($this->data->pilotrating);
            foreach ($pilotRatings as $pr) {
                if (!$member->hasQualification($pr)) {
                    $member->addQualification($pr);
                }
            }
        } catch (DuplicateQualificationException $e) {
            // TOdo: Something.
        }

        return $member;
    }
}
