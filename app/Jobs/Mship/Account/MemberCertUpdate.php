<?php

namespace App\Jobs\Mship\Account;

use App\Jobs\Job;
use DB;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification as QualificationData;
use Carbon\Carbon;
use VatsimXML;
use Exception;

class MemberCertUpdate extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $accountID;

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
        $_m = Account::where('account_id', $this->accountID)->firstOrFail();
        
        // Let's load the details from VatsimXML!
        try {
            $_xmlData = VatsimXML::getData($_m->account_id, "idstatusint");
        } catch (Exception $e) {
            return;
        }

        if ($_xmlData->name_first == new \stdClass() && $_xmlData->name_last == new \stdClass()
            && $_xmlData->email == "[hidden]") {
            $_m->delete();
            return;
        }

        DB::beginTransaction();
        try {
            if (!empty($_xmlData->name_first) && is_string($_xmlData->name_first)) $_m->name_first = $_xmlData->name_first;
            if (!empty($_xmlData->name_last) && is_string($_xmlData->name_last)) $_m->name_last = $_xmlData->name_last;

            $_m->cert_checked_at = Carbon::now()->toDateTimeString();
            $_m->save();
            $_m = $_m->find($_m->account_id);

            // Let's work out the user status.
            $_m->is_inactive = (boolean) ($_xmlData->rating < 0);

            // Are they network banned, but unbanned in our system?
            // Add it!
            if($_xmlData->rating == 0 && $_m->is_network_banned === false){
                // Add a ban.
                $newBan = new \App\Models\Mship\Account\Ban();
                $newBan->type = \App\Models\Mship\Account\Ban::TYPE_NETWORK;
                $newBan->reason_extra = "Network ban discovered via Cert update scripts.";
                $newBan->period_start = \Carbon\Carbon::now();
                $newBan->save();

                $_m->bans()->save($newBan);
                Account::find(VATSIM_ACCOUNT_SYSTEM)->bansAsInstigator($newBan);
            }

            // Are they banned in our system (for a network ban) but unbanned on the network?
            // Then expire the ban.
            if($_m->is_network_banned === true && $_xmlData->rating > 0){
                $ban = $_m->network_ban;
                $ban->period_finish = \Carbon\Carbon::now();
                $ban->setPeriodAmountFromTS();
                $ban->save();
            }

            // Set their VATSIM registration date.
            $_m->joined_at = $newDate;

            // If they're in this feed, they're a division member.
            $_m->determineState($_xmlData->region, $_xmlData->division);

            // Sort their rating(s) out - we're not permitting instructor ratings if they're NONE UK members.
            if(($_xmlData->rating != 8 AND $_xmlData->rating != 9) OR $_m->current_state->state == \App\Models\Mship\Account\State::STATE_DIVISION){
                $atcRating = QualificationData::parseVatsimATCQualification($_xmlData->rating);
                $_m->addQualification($atcRating);
            }

            // If their rating is ABOVE INS1 (8+) then let's get their last.
            if ($_xmlData->rating >= 8) {
                $_prevRat = VatsimXML::getData($_m->account_id, "idstatusprat");
                if (isset($_prevRat->PreviousRatingInt)) {
                    $prevAtcRating = QualificationData::parseVatsimATCQualification($_prevRat->PreviousRatingInt);
                    $_m->addQualification($prevAtcRating);
                }
            } else {
                // remove any extra ratings
                foreach (($q = $_m->qualifications_atc_training) as $qual) {
                    $qual->delete();
                }
                foreach (($q = $_m->qualifications_pilot_training) as $qual) {
                    $qual->delete();
                }
                foreach (($q = $_m->qualifications_admin) as $qual) {
                    $qual->delete();
                }
            }

            $pilotRatings = QualificationData::parseVatsimPilotQualifications($_xmlData->pilotrating);
            foreach ($pilotRatings as $pr) {
                $_m->addQualification($pr);
            }

            $_m->save();

        } catch (Exception $e) {
            DB::rollback();
            print "\tDB::rollback\n";
            print "\tError: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile() . "\n";
            print "\tCID: " . $_m->account_id . "\n";
        }

        DB::commit();
    }
}
